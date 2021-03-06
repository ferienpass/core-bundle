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
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Message\AttendanceStatusChanged;
use Ferienpass\CoreBundle\Message\ParticipantListChanged;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/attendance/{id}", requirements={"id"="\d+"})
 */
final class AttendanceController extends AbstractController
{
    /**
     * @Route("/sort", methods={"POST"})
     */
    public function sortParticipantList(Attendance $attendance, Request $request, Session $session): Response
    {
        $this->get('contao.framework')->initialize();

        $this->checkToken();

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $session->getBag('contao_backend');
        $autoAssign = $sessionBag->get('autoAssign', false);

        $offer = $attendance->getOffer();
        if ($request->request->has('newStatus')) {
            $this->setNewStatus($attendance, $request, $autoAssign);
        } else {
            $this->setNewIndex($offer, $attendance, $request);
        }

        // Update participant list (move-up participants)
        // WHEN the current participant was not added to the waitlist explicitly,
        // otherwise, it might become confirmed immedietly.
        if ($autoAssign && (!$request->request->has('newStatus') || Attendance::STATUS_WAITLISTED !== $request->request->get('newStatus'))) {
            $this->dispatchMessage(new ParticipantListChanged($offer->getId()));
        }

        $this->getDoctrine()->getManager()->flush();

        return new Response('', Response::HTTP_OK);
    }

    private function setNewStatus(Attendance $attendance, Request $request, $autoAssign): void
    {
        $oldStatus = $attendance->getStatus();

        $attendance->setStatus($request->request->get('newStatus'));
        $attendance->setSorting(($request->request->getInt('newIndex') * 128) + 64);

        if ($autoAssign) {
            $this->dispatchMessage(new AttendanceStatusChanged($attendance->getId(), $oldStatus, $attendance->getStatus()));
        }
    }

    private function setNewIndex(Offer $offer, Attendance $attendance, Request $request): void
    {
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
}
