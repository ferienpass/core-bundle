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

namespace Ferienpass\CoreBundle\Controller\Fragment;

use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystems;
use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Facade\AttendanceFacade;
use Ferienpass\CoreBundle\Form\ApplyFormType;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Ferienpass\CoreBundle\Ux\Flash;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationFormController extends AbstractController
{
    public function __construct(private ApplicationSystems $applicationSystems, private AttendanceFacade $attendanceFacade, private AttendanceRepository $attendanceRepository)
    {
    }

    public function __invoke(Offer $offer, Request $request): Response
    {
        if (!$offer->requiresApplication() || !$offer->isOnlineApplication() || $offer->isCancelled()) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $applicationSystem = $this->applicationSystems->findApplicationSystem($offer);

        $countParticipants = $this->attendanceRepository->count(['status' => 'confirmed', 'offer' => $offer]) + $this->attendanceRepository->count(['status' => 'waitlisted', 'offer' => $offer]);
        $vacant = $offer->getMaxParticipants() ?? 0 - $countParticipants;

        $applicationForm = $this->createForm(ApplyFormType::class, null, [
            'offer' => $offer,
            'application_system' => $applicationSystem,
        ]);

        $applicationForm->handleRequest($request);
        if ($applicationForm->isSubmitted() && $applicationForm->isValid()) {
            foreach ($applicationForm->get('participants')->getData() as $participant) {
                $this->attendanceFacade->create($offer, $participant);
            }

            $this->addFlash(...Flash::confirmation()->text('Die Anmeldungen wurden angenommen')->create());

            return $this->redirect($request->getUri());
        }

        return $this->render('@FerienpassCore/Fragment/application_form.html.twig', [
            'offer' => $offer,
            'form' => $applicationForm->createView(),
            'applicationSystem' => $applicationSystem,
            'vacant' => max(0, $vacant),
        ]);
    }
}
