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

class ChartAttendancesController extends AbstractEditionStatsWidgetController
{
    private AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function __invoke(int $id): Response
    {
        return $this->render('@FerienpassCore/Backend/EditionStats/chart_attendances.html.twig', [
            'data' => $this->getData($id),
        ]);
    }

    private function getData(int $passEdition): array
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
            $begin = new \DateTime($days[0]);
            $end = new \DateTime($days[\count($days) - 1]);
            $interval = \DateInterval::createFromDateString('1 day');
        } catch (\Exception $e) {
            return [];
        }

        $return = [];
        $return[(clone $begin)->modify('-1 day')->getTimestamp()] = 0;

        /** @var \DateInterval $dt */
        foreach (new \DatePeriod($begin, $interval, $end) as $dt) {
            $count = $daysAndCount[$dt->format('Y-m-d')] ?? 0;

            $return[$dt->getTimestamp()] = (int)$count;
        }

        return $return;
    }
}
