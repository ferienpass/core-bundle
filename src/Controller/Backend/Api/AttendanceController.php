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

namespace Ferienpass\CoreBundle\Controller\Backend\Api;

use Ferienpass\CoreBundle\Entity\Attendance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/attendance/{id}", requirements={"id"="\d+"})
 */
final class AttendanceController extends AbstractController
{
    /**
     * @Route("/sort", methods={"POST"})
     */
    public function sortParticipantList(Attendance $attendance, Request $request): Response
    {
        $this->get('contao.framework')->initialize();

        $this->checkToken();

        $offer = $attendance->getOffer();
        if ($request->request->has('newStatus')) {
            $attendance->setStatus($request->request->get('newStatus'));
            $attendance->setSorting(($request->request->getInt('newIndex') * 128) + 64);
        } else {
            $attendances = $offer->getAttendancesWithStatus($attendance->getStatus());
            $attendances = array_values($attendances->toArray());

            if ($request->request->has('newIndex')) {
                uasort($attendances, fn (Attendance $a, Attendance $b) => array_search($a, $attendances, true) === $request->request->getInt('newIndex') ? -1 : 0);
            }

            $i = 0;
            foreach ($attendances as $a) {
                $a->setSorting(++$i * 128);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return new Response('', Response::HTTP_OK);
    }
}
