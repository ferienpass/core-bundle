<?php

declare(strict_types=1);

/*
 * This file is part of the Ferienpass package.
 *
 * (c) Richard Henkenjohann <richard@ferienpass.online>
 *
 * For more information visit the project website <https://ferienpass.online>
 * or the documentation under <https://docs.ferienpass.online>.
 */

namespace Ferienpass\CoreBundle\Backend;

use Contao\Config;
use Contao\DataContainer;
use Contao\System;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

class PassEditionStatistics
{
    private Connection $connection;
    private TwigEnvironment $twig;
    private TranslatorInterface $translator;

    public function __construct(Connection $connection, TwigEnvironment $twig, TranslatorInterface $translator)
    {
        $this->connection = $connection;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function execute(DataContainer $dc): string
    {
        $editionId = (int) $dc->id;

        $GLOBALS['TL_CSS']['be_stats'] = 'bundles/ferienpasscore/stats.scss|static';
        $GLOBALS['TL_JAVASCRIPT']['frappe-charts'] =
            'https://cdn.jsdelivr.net/npm/frappe-charts@1.1.0/dist/frappe-charts.min.iife.js';

        return $this->twig->render('@FerienpassCore/Backend/be_statistics.html.twig', [
            'back_href' => System::getReferer(),
            'count_participants' => $this->countParticipants($editionId),
            'count_offers' => $this->countOffersWithVariants($editionId),
            'count_offers_no_variants' => $this->countOffersWithoutVariants($editionId),
            'count_hosts' => $this->countHostsWithOffer($editionId),
            'count_attendances' => $this->countAttendances($editionId),
            'count_attendances_by_status' => $this->countAttendancesByStatus($editionId),
            'count_attendances_by_day' => $this->countAttendancesByDay($editionId),
            'count_attendances_by_age' => $this->countAttendancesByParticipantAge($editionId),
            'count_attendances_by_category' => $this->countAttendancesByCategory($editionId),
            'utilization_by_offer' => $this->getUtilization($editionId),
        ]);
    }

    private function countParticipants(int $passEdition): int
    {
        return (int) $this->connection
            ->executeQuery(sprintf('SELECT COUNT(DISTINCT a.participant_id) FROM Attendance a INNER JOIN Offer f ON a.offer_id=f.id WHERE f.edition=%d', $passEdition))
            ->fetchOne();
    }

    private function countAttendancesByCategory(int $passEdition): ?array
    {
        return [];
        $count = $this->connection
            ->executeQuery(sprintf("SELECT c.name as category,COUNT(a.id) as count FROM Attendance a INNER JOIN Offer f ON a.offer_id=f.id INNER JOIN OfferCategory c ON c.id=f.category WHERE f.edition=%d AND a.status<>'withdrawn' GROUP BY c.id", $passEdition))
            ->fetchAll(\PDO::FETCH_BOTH);

        $countConfirmed = $this->connection
            ->executeQuery(sprintf("SELECT c.name as category,COUNT(a.id) as count FROM Attendance a INNER JOIN Offer f ON a.offer=f.id INNER JOIN OfferCategory c ON c.id=f.category WHERE f.edition=%d AND a.status='confirmed' GROUP BY c.id", $passEdition))
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $return = [];
        foreach ($count as $i => $v) {
            $return[] = [
                'category' => $v['category'],
                'count' => (int) $v['count'],
                'count_confirmed' => (int) $countConfirmed[$v['category']],
            ];
        }

        return $return;
    }

    private function countOffersWithVariants(int $passEdition): int
    {
        return (int) $this->connection
            ->executeQuery(sprintf('SELECT COUNT(f.id) FROM Offer f WHERE f.edition=%d', $passEdition))
            ->fetchOne();
    }

    private function countOffersWithoutVariants(int $passEdition): int
    {
        return (int) $this->connection
            ->executeQuery(sprintf('SELECT COUNT(f.id) FROM Offer f WHERE f.varbase=1 AND f.edition=%d', $passEdition))
            ->fetchOne();
    }

    private function countHostsWithOffer(int $passEdition): int
    {
        return 0;

        return (int) $this->connection
            ->executeQuery(
                sprintf(
                    'SELECT COUNT(DISTINCT f.host) FROM Offer f WHERE f.edition=%d',
                    $passEdition
                )
            )
            ->fetchOne();
    }

    private function countAttendances(int $passEdition): int
    {
        return (int) $this->connection
            ->executeQuery("SELECT COUNT(*) FROM Attendance a INNER JOIN Offer f ON a.offer_id=f.id WHERE f.edition=:id AND status<>'withdrawn'", ['id' => $passEdition])
            ->fetchOne();
    }

    private function countAttendancesByDay(int $passEdition): array
    {
        return [];
        $daysAndCount = $this->connection
            ->executeQuery(
                "SELECT FROM_UNIXTIME(a.created, '%%Y-%%m-%%d') AS day, COUNT(*) FROM Attendance a INNER JOIN Offer f ON a.offer_id=f.id WHERE f.edition=:id GROUP BY DAY(day) ORDER BY day",
                ['id' => $passEdition]
            )
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        if ([] === $daysAndCount) {
            return [];
        }

        try {
            $days = array_keys($daysAndCount);
            $begin = new DateTime($days[0]);
            $end = new DateTime($days[\count($days) - 1]);
            $interval = DateInterval::createFromDateString('1 day');
        } catch (\Exception $e) {
            return [];
        }

        $return = [
            [
                'key' => (clone $begin)->modify('-1 day')->format($this->getDateFormat()),
                'value' => 0,
            ],
        ];

        $sum = 0;
        /** @var DateInterval $dt */
        foreach (new DatePeriod($begin, $interval, $end) as $dt) {
            $count = $daysAndCount[$dt->format('Y-m-d')] ?? 0;
            $sum = $count + $sum;

            $return[] = [
                'key' => $dt->format($this->getDateFormat()),
                'value' => $sum,
            ];
        }

        return $return;
    }

    private function countAttendancesByStatus(int $passEdition): array
    {
        try {
            $statusAndCount = $this->connection
                ->query(
                    sprintf(
                        'SELECT a.status,COUNT(*) as count FROM tl_ferienpass_attendance a INNER JOIN Offer f ON a.offer=f.id WHERE f.edition=%d GROUP BY a.status',
                        $passEdition
                    )
                )
                ->fetchAll(\PDO::FETCH_KEY_PAIR);
        } catch (DBALException $e) {
            return [];
        }

        $return = [];

        foreach (['confirmed', 'waitlisted', 'error', 'waiting', 'withdrawn'] as $status) {
            $return[] = [
                'title' => $this->translate('MSC.attendance_status.'.$status),
                'count' => (int) ($statusAndCount[$status] ?? 0),
            ];
        }

        return $return;
    }

    private function countAttendancesByParticipantAge(int $passEdition): array
    {
        $ageAndCount = $this->connection->query(
            sprintf(
                "SELECT (CASE WHEN (aa.age IS NULL) THEN 'N/A' ELSE aa.age END) as age, COUNT(*) as count FROM tl_ferienpass_attendance a INNER JOIN Offer f ON f.id = a.offer INNER JOIN tl_ferienpass_attendance_age aa ON a.id = aa.attendance WHERE f.edition = %d AND a.status<>'withdrawn' GROUP BY aa.age",
                $passEdition
            )
        )->fetchAll(\PDO::FETCH_BOTH);

        $return = [];
        foreach ($ageAndCount as $k) {
            $return[] = [
                'title' => is_numeric($k['age']) ? sprintf('%d Jahre', $k['age']) : $k['age'],
                'count' => (int) $k['count'],
            ];
        }

        if ('N/A' === $return[0]['title']) {
            $return[] = array_shift($return);
        }

        return $return;
    }

    private function getUtilization(int $passEdition): array
    {
        $result = $this->connection->query(
            sprintf(
                <<<'SQL'
SELECT a.status                          as status,
       f.id                              as offer_id,
       f.name                            as offer_title,
       d.start                           as date_start,
       f.maxParticipants             as offer_max,
       COUNT(a.id)                       as count_applications,
       COUNT(a.id) / f.maxParticipants as utilization
FROM tl_ferienpass_attendance a
         INNER JOIN Offer f ON f.id = a.offer
         LEFT JOIN tl_metamodel_offer_date d ON d.item_id = f.id
WHERE f.edition = %d
  AND f.cancelled <> 1
GROUP BY f.id, a.status, d.start

SQL
                ,
                $passEdition
            )
        )->fetchAll(\PDO::FETCH_BOTH);

        $labels = [];
        $overall = [];

        $confirmed = [];
        $waitlisted = [];
        $error = [];
        $waiting = [];

        $offerIds = array_unique(array_column($result, 'offer_id'));

        foreach (['confirmed', 'waitlisted', 'error', 'waiting'] as $status) {
            $utilizationOfStatus = array_filter(
                $result,
                static function ($c) use ($status) {
                    return $status === $c['status'];
                }
            );

            foreach ($offerIds as $i => $offerId) {
                $utilizationOfStatusAndOffer = array_filter(
                    $utilizationOfStatus,
                    static function ($c) use ($offerId) {
                        return (int) $c['offer_id'] === (int) $offerId;
                    }
                );

                if ([] !== $utilizationOfStatusAndOffer && null !== $a = array_pop($utilizationOfStatusAndOffer)) {
                    $$status[$i] = (float) $a['utilization'];
                    $labels[$i] = sprintf(
                        '%s: %s (max. %d)',
                        $a['offer_title'],
                        date($this->getDateFormat(), (int) $a['date_start']),
                        $a['offer_max']
                    );
                    $overall[$i] += (float) $a['utilization'];
                } else {
                    $$status[$i] = 0;
                    $overall[$i] += 0;
                    if (!isset($labels[$i])) {
                        $labels[$i] = '';
                    }
                    if (!isset($overall[$i])) {
                        $overall[$i] = 0;
                    }
                }
            }
        }

        array_multisort(
            $overall,
            \SORT_DESC,
            \SORT_NUMERIC,
            $confirmed,
            $waitlisted,
            $error,
            $waiting,
            $labels
        );

        $return['labels'] = array_values($labels);
        foreach (['confirmed', 'waitlisted', 'error', 'waiting'] as $status) {
            $return['datasets'][] = [
                'name' => $this->translate('MSC.attendance_status.'.$status),
                'values' => array_values($$status),
            ];
        }

        return $return;
    }

    private function translate(string $key): string
    {
        return $this->translator->trans($key, [], 'contao_default');
    }

    private function getDateFormat(): string
    {
        return (string) Config::get('dateFormat');
    }
}
