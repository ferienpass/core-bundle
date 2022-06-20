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

namespace Ferienpass\CoreBundle\Controller\EditionStats;

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChartUtilizationController extends AbstractEditionStatsWidgetController
{
    public function __construct(private AttendanceRepository $attendanceRepository, private TranslatorInterface $translator)
    {
    }

    public function __invoke(int $id): Response
    {
        return $this->render('@FerienpassCore/Backend/EditionStats/chart_utilization.html.twig', [
            'data' => $this->getData($id),
        ]);
    }

    private function getData(int $passEdition): array
    {
        $return = [];
        $result = $this->attendanceRepository->createQueryBuilder('a')
            ->select('a.status')
            ->addSelect('o.id as offer_id')
            ->addSelect('o.name as offer_title')
            ->addSelect('MIN(d.begin) as date_start')
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
                if ([] !== $utilizationOfStatusAndOffer && null !== ($a = ($utilizationOfStatusAndOffer[0] ?? null)) && !$a['date_start'] instanceof \DateTimeInterface) {
                    ${$status}[$i] = (float) $a['utilization'];
                    $overall[$i] += (float) $a['utilization'];

                    $labels[$i] = sprintf('%s: %s (max. %d)', $a['offer_title'], $a['date_start'] ? (new \DateTime($a['date_start']))->format($GLOBALS['TL_CONFIG']['dateFormat']) : '', $a['offer_max']);
                } else {
                    ${$status}[$i] = 0;
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
                'name' => $this->translator->trans('MSC.attendance_status.'.$status, [], 'contao_default'),
                'values' => array_values(${$status}),
            ];
        }

        return $return;
    }
}
