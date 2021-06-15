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
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\OptIn\OptIn;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\FrontendUser;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Message\AccountActivated;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class RegistrationActivateController extends AbstractController
{
    private UserProviderInterface $userProvider;
    private TokenStorageInterface $tokenStorage;
    private LoggerInterface $logger;
    private EventDispatcherInterface $eventDispatcher;
    private UserCheckerInterface $userChecker;
    private AuthenticationSuccessHandlerInterface $authenticationSuccessHandler;
    private OptIn $optIn;
    private TokenChecker $tokenChecker;

    public function __construct(
        UserProviderInterface $userProvider,
        TokenStorageInterface $tokenStorage,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        UserCheckerInterface $userChecker,
        AuthenticationSuccessHandlerInterface $authenticationSuccessHandler,
        OptIn $optIn,
        TokenChecker $tokenChecker
    ) {
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->userChecker = $userChecker;
        $this->authenticationSuccessHandler = $authenticationSuccessHandler;
        $this->optIn = $optIn;
        $this->tokenChecker = $tokenChecker;
    }

    public function __invoke(Request $request): Response
    {
        if ((null === $token = $request->query->get('token')) || 0 !== strncmp($token, 'reg-', 4)) {
            throw new PageNotFoundException('Invalid token');
        }

        if (null !== $this->tokenChecker->getFrontendUsername()) {
            return $this->redirectToRoute('registration_welcome');
        }

        // Find an unconfirmed token with only one related record
        if ((!$optInToken = $this->optIn->find($token)) || !$optInToken->isValid()
            || 1 !== \count($relatedRecords = $optInToken->getRelatedRecords())
            || 'tl_member' !== key($relatedRecords)
            || 1 !== \count($arrIds = current($relatedRecords))
            || (!$memberModel = MemberModel::findByPk($arrIds[0]))) {
            $error = $GLOBALS['TL_LANG']['MSC']['invalidToken'];

            return $this->render('@FerienpassCore/Fragment/message.html.twig', ['error' => $error]);
        }

        if ($optInToken->isConfirmed()) {
            $error = $GLOBALS['TL_LANG']['MSC']['tokenConfirmed'];

            return $this->render('@FerienpassCore/Fragment/message.html.twig', ['error' => $error]);
        }

        if ($optInToken->getEmail() !== $memberModel->email) {
            $error = $GLOBALS['TL_LANG']['MSC']['tokenEmailMismatch'];

            return $this->render('@FerienpassCore/Fragment/message.html.twig', ['error' => $error]);
        }

        /** @psalm-suppress InvalidPropertyAssignmentValue */
        $memberModel->disable = '';
        $memberModel->save();

        $optInToken->confirm();

        $this->logger->info('User account ID {id} has been activated', ['id' => $memberModel->id]);

        $this->loginUser($memberModel->username, $request);

        $this->dispatchMessage(new AccountActivated((int) $memberModel->id));

        return $this->redirectToRoute('registration_welcome');
    }

    private function loginUser(string $username, Request $request): void
    {
        try {
            $user = $this->userProvider->loadUserByUsername($username);
        } catch (UsernameNotFoundException $exception) {
            return;
        }

        if (!$user instanceof FrontendUser) {
            return;
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->userChecker->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            return;
        }

        $usernamePasswordToken = new UsernamePasswordToken($user, null, 'frontend', $user->getRoles());
        $this->tokenStorage->setToken($usernamePasswordToken);

        $event = new InteractiveLoginEvent($request, $usernamePasswordToken);
        $this->eventDispatcher->dispatch($event);

        $this->logger->info(sprintf('User "%s" was logged in automatically after account activation', $username));

        $request->request->set('_target_path', base64_encode($request->getRequestUri()));

        $this->authenticationSuccessHandler->onAuthenticationSuccess($request, $usernamePasswordToken);
    }
}
