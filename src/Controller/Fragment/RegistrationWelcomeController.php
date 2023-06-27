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

use Contao\CoreBundle\Controller\AbstractController;
use Contao\FrontendUser;
use Contao\MemberModel;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Form\UserParticipantsType;
use Ferienpass\CoreBundle\Ux\Flash;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrationWelcomeController extends AbstractController
{
    public function __construct(private ManagerRegistry $doctrine, private FormFactoryInterface $formFactory)
    {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof FrontendUser) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $em = $this->doctrine->getManager();
        $member = MemberModel::findByPk($user->id);

        $form = $this->formFactory->create(UserParticipantsType::class, null, ['member' => $member]);

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
                ->text('Nun können Sie loslegen und Ihre Kinder zu Ferienpass-Angeboten anmelden.')
                ->href($this->generateUrl('offer_list'))
                ->linkText('Zu den Angeboten')
                ->create()
            );

            return $this->redirectToRoute($request->attributes->get('_route') ?: 'personal_data');
        }

        return $this->render('@FerienpassCore/Fragment/registration_welcome.html.twig', [
            'form' => $form,
        ]);
    }
}
