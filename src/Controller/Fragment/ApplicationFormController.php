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

use Contao\CoreBundle\OptIn\OptIn;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystems;
use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Facade\AttendanceFacade;
use Ferienpass\CoreBundle\Form\ApplyFormParticipantType;
use Ferienpass\CoreBundle\Form\ApplyFormType;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Ferienpass\CoreBundle\Ux\Flash;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApplicationFormController extends AbstractController
{
    public function __construct(private ApplicationSystems $applicationSystems, private AttendanceFacade $attendanceFacade, private AttendanceRepository $attendanceRepository, private ManagerRegistry $doctrine, private OptIn $optIn, private FormFactoryInterface $formFactory)
    {
    }

    public function __invoke(Offer $offer, Request $request): Response
    {
        if ($request->query->has('token') && ($optInToken = $this->optIn->find($request->query->get('token'))) && $optInToken->isValid()) {
            $optInToken->confirm();

            return new RedirectResponse($this->generateUrl($request->attributes->get('_route'), ['alias' => $offer->getAlias()]));
        }

        if (!$offer->requiresApplication() || !$offer->isOnlineApplication() || $offer->isCancelled()) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $applicationSystem = $this->applicationSystems->findApplicationSystem($offer);
        if (null === $applicationSystem) {
            return $this->render('@FerienpassCore/Fragment/application_form.html.twig', ['offer' => $offer]);
        }

        $countParticipants = $this->attendanceRepository->count(['status' => 'confirmed', 'offer' => $offer]) + $this->attendanceRepository->count(['status' => 'waitlisted', 'offer' => $offer]);
        $vacant = $offer->getMaxParticipants() > 0 ? $offer->getMaxParticipants() - $countParticipants : null;

        $user = $this->getUser();
        $allowAnonymous = (bool) $applicationSystem->getTask()?->isAllowAnonymous();
        $allowAnonymousFee = (bool) $applicationSystem->getTask()?->isAllowAnonymousFee();
        $participantForm = $this->formFactory->create(ApplyFormParticipantType::class);
        $applicationForm = $this->formFactory->create(ApplyFormType::class, null, [
            'offer' => $offer,
            'application_system' => $applicationSystem,
        ]);

        $participantForm->handleRequest($request);
        if ($participantForm->isSubmitted() && $participantForm->isValid() && ($user || $allowAnonymous)) {
            return $this->handleSubmitParticipant($participantForm->get('participant')->getData(), $offer, $request);
        }

        $applicationForm->handleRequest($request);
        if ($applicationForm->isSubmitted() && $applicationForm->isValid() && ($user || (!$offer->getFee() && $allowAnonymous) || ($offer->getFee() && $allowAnonymousFee))) {
            return $this->handleSubmitApplications($applicationForm->get('participants')->getData(), $offer, $request);
        }

        return $this->render('@FerienpassCore/Fragment/application_form.html.twig', [
            'offer' => $offer,
            'form' => $applicationForm->createView(),
            'newParticipant' => $participantForm->createView(),
            'applicationSystem' => $applicationSystem,
            'vacant' => null === $vacant ? null : max(0, $vacant),
        ]);
    }

    private function handleSubmitApplications(iterable $participants, Offer $offer, Request $request): Response
    {
        foreach ($participants as $participant) {
            $this->attendanceFacade->create($offer, $participant);
        }

        $this->addFlash(...Flash::confirmation()->text('Die Anmeldungen wurden angenommen')->create());

        return $this->redirect($request->getUri());
    }

    private function handleSubmitParticipant(Participant $participant, Offer $offer, Request $request): Response
    {
        $this->doctrine->getManager()->persist($participant);
        $this->doctrine->getManager()->flush();

        if (null === $participant->getMember()) {
            $request->getSession()->set('participant_ids', array_unique(array_merge($request->getSession()->get('participant_ids', []), [$participant->getId()])));

            // Verify email address
            $optInToken = $this->optIn->create('apply', $participant->getEmail(), ['Participant' => [$participant->getId()]]);
            $optInToken->send(
                'Bitte bestätigen Sie Ihre E-Mail-Adresse',
                sprintf(
                    "Bitte bestätigen Sie Ihre E-Mail-Adresse für die Anmeldung beim Ferienpass\n\n\n%s",
                    $this->generateUrl($request->attributes->get('_route'), ['alias' => $offer->getAlias(), 'token' => $optInToken->getIdentifier()], UrlGeneratorInterface::ABSOLUTE_URL)
                )
            );
        }

        $this->addFlash(...Flash::confirmationModal()
            ->headline('Los geht\'s!')
            ->text('Nun können Sie loslegen und sich zu Ferienpass-Angeboten anmelden.')
            ->linkText('Zurück zum Angebot')
            ->create()
        );

        return $this->redirect($request->getUri());
    }
}
