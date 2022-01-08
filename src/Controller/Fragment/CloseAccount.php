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
use Contao\CoreBundle\Exception\ResponseException;
use Contao\FormTextField;
use Contao\FrontendUser;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Message\AccountDeleted;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CloseAccount extends AbstractController
{
    private UserPasswordHasherInterface $passwordHasher;
    private LoggerInterface $logger;
    private MessageBusInterface $messageBus;

    public function __construct(UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger, MessageBusInterface $messageBus)
    {
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
        $this->messageBus = $messageBus;
    }

    public function __invoke(Request $request, Session $session): Response
    {
        $user = $this->getUser();
        if (!$user instanceof FrontendUser) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $passwordField = [
            'name' => 'password',
            'inputType' => 'text',
            'label' => $GLOBALS['TL_LANG']['MSC']['password'][0],
            'eval' => ['hideInput' => true, 'preserveTags' => true, 'mandatory' => true, 'required' => true],
        ];

        $passwordWidget =
            new FormTextField(FormTextField::getAttributesFromDca($passwordField, $passwordField['name']));

        if ('close_account' === $request->request->get('FORM_SUBMIT')) {
            $passwordWidget->validate();

            if (!$passwordWidget->hasErrors()
                && !$this->passwordHasher->isPasswordValid($user, $passwordWidget->value)) {
                $passwordWidget->value = '';
                $passwordWidget->addError($GLOBALS['TL_LANG']['ERR']['invalidPass']);
            }

            if (!$passwordWidget->hasErrors()) {
                if (null !== $memberModel = MemberModel::findByPk($user->id)) {
                    $memberModel->delete();

                    $this->logger->info(sprintf('User account ID %u has been deleted', $user->id));
                }

                $this->container->get('security.token_storage')->setToken();
                $session->invalidate();

                $this->messageBus->dispatch(new AccountDeleted((int) $user->id));

                throw new ResponseException($this->redirectToRoute('account_deleted'));
            }

            throw new ResponseException(new JsonResponse(['error' => $passwordWidget->getErrorAsString()], Response::HTTP_BAD_REQUEST));
        }

        return $this->render('@FerienpassCore/Fragment/close_account.html.twig', []);
    }
}
