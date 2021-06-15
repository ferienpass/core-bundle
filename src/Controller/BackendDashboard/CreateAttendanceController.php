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

namespace Ferienpass\CoreBundle\Controller\BackendDashboard;

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Facade\AttendanceFacade;
use Ferienpass\CoreBundle\Form\BackendApplicationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateAttendanceController extends AbstractDashboardWidgetController
{
    private AttendanceFacade $attendanceFacade;

    public function __construct(AttendanceFacade $attendanceFacade)
    {
        $this->attendanceFacade = $attendanceFacade;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(BackendApplicationType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $offer = $form->get('offer')->getData();
            $participant = $form->get('participant')->getData();
            $status = $form->get('status')->getData();
            $notify = (bool) $form->get('notify')->getData();

            \assert($offer instanceof Offer);

            if (!$participant instanceof Participant) {
                $participant = new Participant();
                $participant->setFirstname($form->get('firstname')->getData());
                $participant->setLastname($form->get('lastname')->getData());
                $participant->setDateOfBirth($form->get('dateOfBirth')->getData());
                $participant->setPhone($form->get('phone')->getData());
                $participant->setMobile($form->get('mobile')->getData());
                $participant->setEmail($form->get('email')->getData());

                $this->getDoctrine()->getManager()->persist($participant);
            }

            $this->attendanceFacade->create($offer, $participant, $status, $notify);

            return $this->redirectToRoute('backend_offer_applications', ['id' => $offer->getId()]);
        }

        return $this->render('@FerienpassCore/Backend/Dashboard/create_attendance.html.twig', [
            'application' => $form->createView(),
        ]);
    }
}
