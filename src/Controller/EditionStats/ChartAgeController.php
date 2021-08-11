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

class ChartAgeController extends AbstractEditionStatsWidgetController
{
    private AttendanceRepository $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function __invoke(int $id): Response
    {
        return $this->render('@FerienpassCore/Backend/EditionStats/chart_age.html.twig', [
            'data' => $this->getData($id),
        ]);
    }

    private function getData(int $passEdition): array
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
}
