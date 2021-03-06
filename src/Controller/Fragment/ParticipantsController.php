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

use Contao\FrontendUser;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Form\UserParticipantsType;
use Ferienpass\CoreBundle\Repository\ParticipantRepository;
use Ferienpass\CoreBundle\Ux\Flash;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ParticipantsController extends AbstractController
{
    private ParticipantRepository $participantRepository;

    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof FrontendUser) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $em = $this->getDoctrine()->getManager();

        // TODO if originalParticipants.length eq 0 then add constraint {MinLength=1}
        $originalParticipants = $this->participantRepository->findBy(['member' => $user->id]);
        $form = $this->createForm(UserParticipantsType::class, null, ['member' => MemberModel::findByPk($user->id)]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var iterable<int, Participant> $participants */
            $participants = $form->get('participants')->getData();

            foreach ($participants as $participant) {
                $em->persist($participant);
            }

            $em->flush();

            $this->addFlash(...Flash::confirmationModal()
                ->headline('Los geht\'s!')
                ->text('Nun k??nnen Sie loslegen und Ihre Kinder zu Ferienpass-Angeboten anmelden.')
                ->href($this->generateUrl('offer_list'))
                ->linkText('Zu den Angeboten')
                ->create()
            );

            return $this->redirectToRoute($request->get('_route') ?: 'personal_data');
        }

        return $this->render('@FerienpassCore/Fragment/participants.html.twig', [
            'participants' => $originalParticipants,
            'form' => $form->createView(),
        ]);
    }
}
