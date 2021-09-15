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

class ChartCategoriesController extends AbstractEditionStatsWidgetController
{
    private AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function __invoke(int $id): Response
    {
        $data = $this->getData($id);
        if (empty($data)) {
            return new Response();
        }

        return $this->render('@FerienpassCore/Backend/EditionStats/chart_categories.html.twig', [
            'data' => $data,
        ]);
    }

    private function getData(int $passEdition): ?array
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
}
