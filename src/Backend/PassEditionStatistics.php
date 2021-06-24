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
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment as TwigEnvironment;

class PassEditionStatistics
{
    private TwigEnvironment $twig;
    private TranslatorInterface $translator;

    private OfferRepository $offerRepository;
    private AttendanceRepository $attendanceRepository;

    public function __construct(TwigEnvironment $twig, TranslatorInterface $translator, OfferRepository $offerRepository, AttendanceRepository $attendanceRepository)
    {
        $this->twig = $twig;
        $this->translator = $translator;
        $this->offerRepository = $offerRepository;
        $this->attendanceRepository = $attendanceRepository;
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
        return (int) $this->attendanceRepository->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.participant) AS count')
            ->innerJoin('a.offer', 'o')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countAttendancesByCategory(int $passEdition): ?array
    {
        $qb = $this->attendanceRepository->createQueryBuilder('a')
            ->select('c.name AS category', 'COUNT(a.id) AS count')
            ->innerJoin('a.offer', 'o')
            ->innerJoin('o.categories', 'c')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->groupBy('c.id')
        ;
        $count = (clone $qb)
            ->andWhere('a.status <> :status')
            ->setParameter('status', Attendance::STATUS_WITHDRAWN)
            ->getQuery()
            ->getScalarResult()
        ;
        $countConfirmed = (clone $qb)
            ->andWhere('a.status = :status')
            ->setParameter('status', Attendance::STATUS_CONFIRMED)
            ->getQuery()
            ->getScalarResult()
        ;

        // Transform array to key=>value structure
        $countConfirmed = array_combine(array_column($countConfirmed, 'category'), array_column($countConfirmed, 'count'));

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
        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(o.id) AS count')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->getQuery()
            ->getSingleResult()
        ;
    }

    private function countOffersWithoutVariants(int $passEdition): int
    {
        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(o.id) AS count')
            ->andWhere('o.variantBase IS NULL')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countHostsWithOffer(int $passEdition): int
    {
        return (int) $this->offerRepository->createQueryBuilder('o')
            ->select('COUNT(DISTINCT h.id) AS count')
            ->innerJoin('o.hosts', 'h')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countAttendances(int $passEdition): int
    {
        return (int) $this->attendanceRepository->createQueryBuilder('a')
            ->select('COUNT(a.id) AS count')
            ->innerJoin('a.offer', 'o')
            ->andWhere('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->andWhere('a.status <> :status')
            ->setParameter('status', Attendance::STATUS_WITHDRAWN)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private function countAttendancesByDay(int $passEdition): array
    {
        $daysAndCount = $this->attendanceRepository->createQueryBuilder('a')
            ->select("DATE_FORMAT(a.createdAt, '%Y-%m-%d') AS day")
            ->addSelect('COUNT(a.id) as count')
            ->innerJoin('a.offer', 'o')
            ->where('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->andWhere('a.status <> :status')
            ->setParameter('status', Attendance::STATUS_WITHDRAWN)
            ->groupBy('day')
            ->orderBy('day')
            ->getQuery()
            ->getScalarResult()
        ;

        if ([] === $daysAndCount) {
            return [];
        }

        // Transform array to key=>value structure
        $daysAndCount = array_combine(array_column($daysAndCount, 'day'), array_column($daysAndCount, 'count'));

        try {
            $days = array_keys($daysAndCount);
            $begin = new DateTime($days[0]);
            $end = new DateTime($days[\count($days) - 1]);
            $interval = DateInterval::createFromDateString('1 day');
        } catch (\Exception $e) {
            return [];
        }

        $return = [[
            'key' => (clone $begin)->modify('-1 day')->format($this->getDateFormat()),
            'value' => 0,
        ]];

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
        $statusAndCount = $this->attendanceRepository->createQueryBuilder('a')
            ->select('a.status')
            ->addSelect('COUNT(a.id) as count')
            ->innerJoin('a.offer', 'o')
            ->where('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->groupBy('a.status')
            ->getQuery()
            ->getScalarResult()
        ;

        // Transform array to key=>value structure
        $statusAndCount = array_combine(array_column($statusAndCount, 'status'), array_column($statusAndCount, 'count'));

        $return = [];

        foreach ([Attendance::STATUS_CONFIRMED, Attendance::STATUS_WAITLISTED, Attendance::STATUS_ERROR, Attendance::STATUS_WAITING, Attendance::STATUS_WITHDRAWN] as $status) {
            $return[] = [
                'title' => $this->translate('MSC.attendance_status.'.$status),
                'count' => (int) ($statusAndCount[$status] ?? 0),
            ];
        }

        return $return;
    }

    private function countAttendancesByParticipantAge(int $passEdition): array
    {
        $ageAndCount = $this->attendanceRepository->createQueryBuilder('a')
            ->select(<<<'SQL'
(
    CASE
        WHEN (p.dateOfBirth IS NULL AND a.age IS NOT NULL) THEN a.age
        WHEN (p.dateOfBirth IS NOT NULL) THEN TIMESTAMPDIFF(YEAR, p.dateOfBirth, ANY_VALUE(d.begin))
        ELSE 'N/A' END
    ) as age
SQL
            )->addSelect('COUNT(a.id) as count')
            ->innerJoin('a.offer', 'o')
            ->leftJoin('a.participant', 'p')
            ->leftJoin('o.dates', 'd')
            ->where('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->andWhere('a.status <> :status')
            ->setParameter('status', Attendance::STATUS_WITHDRAWN)
            ->orderBy('age')
            ->groupBy('age', 'p.dateOfBirth')
            ->getQuery()
            ->getScalarResult()
        ;

        // Transform array to key=>value structure
        $ageAndCount = array_combine(array_column($ageAndCount, 'age'), array_column($ageAndCount, 'count'));

        $return = [];
        foreach ($ageAndCount as $age => $count) {
            $return[] = [
                'title' => is_numeric($age) ? sprintf('%d Jahre', $age) : $age,
                'count' => (int) $count,
            ];
        }

        if ('N/A' === $return[0]['title']) {
            $return[] = array_shift($return);
        }

        return $return;
    }

    private function getUtilization(int $passEdition): array
    {
        $result = $this->attendanceRepository->createQueryBuilder('a')
            ->select('a.status')
            ->addSelect('o.id as offer_id')
            ->addSelect('o.name as offer_title')
            ->addSelect('ANY_VALUE(d.begin) as date_start')
            ->addSelect('o.maxParticipants as offer_max')
            ->addSelect('COUNT(a.id) as count_applications')
            ->addSelect('COUNT(a.id) / o.maxParticipants as utilization')
            ->innerJoin('a.offer', 'o')
            ->leftJoin('o.dates', 'd')
            ->where('o.edition = :edition')
            ->setParameter('edition', $passEdition)
            ->andWhere("o.cancelled <> '1'")
            ->groupBy('o.id', 'a.status')
            ->getQuery()
            ->getScalarResult()
        ;

        $labels = [];
        $overall = [];

        $confirmed = [];
        $waitlisted = [];
        $error = [];
        $waiting = [];

        $offerIds = array_values(array_unique(array_column($result, 'offer_id')));

        foreach ([Attendance::STATUS_CONFIRMED, Attendance::STATUS_WAITLISTED, Attendance::STATUS_ERROR, Attendance::STATUS_WAITING] as $status) {
            $utilizationOfStatus = array_filter($result, fn ($c) => $status === $c['status']);

            foreach ($offerIds as $i => $offerId) {
                $utilizationOfStatusAndOffer = array_values(array_filter($utilizationOfStatus, fn ($c) => (int) $c['offer_id'] === (int) $offerId));
                if ([] !== $utilizationOfStatusAndOffer &&
                    null !== ($a = ($utilizationOfStatusAndOffer[0] ?? null))
                    && !$a['date_start'] instanceof \DateTimeInterface) {
                    $$status[$i] = (float) $a['utilization'];
                    $labels[$i] = sprintf(
                        '%s: %s (max. %d)',
                        $a['offer_title'],
                        (new DateTime($a['date_start']))->format($this->getDateFormat()),
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
        foreach ([Attendance::STATUS_CONFIRMED, Attendance::STATUS_WAITLISTED, Attendance::STATUS_ERROR, Attendance::STATUS_WAITING] as $status) {
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
