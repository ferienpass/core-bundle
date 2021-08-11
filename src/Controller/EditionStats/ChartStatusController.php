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

class ChartStatusController extends AbstractEditionStatsWidgetController
{
    private AttendanceRepository $attendanceRepository;
    private TranslatorInterface $translator;

    public function __construct(AttendanceRepository $attendanceRepository, TranslatorInterface $translator)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->translator = $translator;
    }

    public function __invoke(int $id): Response
    {
        return $this->render('@FerienpassCore/Backend/EditionStats/chart_status.html.twig', [
            'data' => $this->getData($id),
        ]);
    }

    private function getData(int $passEdition): array
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
                'title' => $this->translator->trans('MSC.attendance_status.'.$status, [], 'contao_default'),
                'count' => (int) ($statusAndCount[$status] ?? 0),
            ];
        }

        return $return;
    }
}
