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

use Contao\CoreBundle\Controller\AbstractFragmentController;
use Contao\FrontendUser;
use Ferienpass\CoreBundle\Form\UserChangePasswordType;
use Ferienpass\CoreBundle\Ux\Flash;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatableMessage;

final class ChangePasswordController extends AbstractFragmentController
{
    public function __construct(private readonly PasswordHasherInterface $passwordHasher, private readonly Security $security, private readonly FormFactoryInterface $formFactory)
    {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user instanceof FrontendUser) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $form = $this->formFactory->create(UserChangePasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->tstamp = time();
            $user->password = $this->passwordHasher->hash($form->get('password')->getData() ?? '');
            $user->save();

            $this->addFlash(...Flash::confirmation()->text(new TranslatableMessage('MSC.newPasswordSet', [], 'contao_default'))->create());
        }

        return $this->renderForm('@FerienpassCore/Fragment/user_account/change_password.html.twig', ['form' => $form]);
    }
}
