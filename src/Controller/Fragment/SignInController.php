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
use Contao\CoreBundle\Security\Exception\LockedException;
use Contao\FrontendUser;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Form\UserLoginType;
use Ferienpass\CoreBundle\Form\UserRegistrationType;
use Ferienpass\CoreBundle\Message\AccountCreated;
use Ferienpass\CoreBundle\Message\AccountResendActivation;
use Scheb\TwoFactorBundle\Security\Authentication\Exception\InvalidTwoFactorCodeException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvent;
use Scheb\TwoFactorBundle\Security\TwoFactor\Event\TwoFactorAuthenticationEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatableMessage;

class SignInController extends AbstractController
{
    private EncoderFactoryInterface $encoderFactory;
    private AuthenticationUtils $authenticationUtils;

    public function __construct(EncoderFactoryInterface $encoderFactory, AuthenticationUtils $authenticationUtils)
    {
        $this->encoderFactory = $encoderFactory;
        $this->authenticationUtils = $authenticationUtils;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        if ($user instanceof FrontendUser) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $targetPath = $this->findTargetPath($request);
        $loginForm = $this->createForm(UserLoginType::class, null, ['target_path' => base64_encode($targetPath)]);

        $registrationForm = $this->createForm(UserRegistrationType::class, new MemberModel());
        $registrationForm->handleRequest($request);
        if ($response = $this->handleRegistrationForm($registrationForm)) {
            return $response;
        }

        return $this->render('@FerienpassCore/Fragment/login.html.twig', [
            'login' => $loginForm->createView(),
            'registration' => $registrationForm->createView(),
        ]);
    }

    private function handleRegistrationForm(FormInterface $form): ?Response
    {
        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }

        $member = $form->getData();
        \assert($member instanceof MemberModel);

        if (null !== ($unactivated = MemberModel::findUnactivatedByEmail($member->email))) {
            $this->resendActivationMail($unactivated);

            return $this->redirectToRoute('registration_confirm');
        }

        if (null !== $existing = MemberModel::findActiveByEmailAndUsername($member->email)) {
            $form->addError(
                new FormError('Ein Konto mit dieser E-Mail-Adresse besteht bereits. Versuchen Sie sich, anzumelden oder Ihr Passwort zurückzusetzen.')
            );

            return null;
        }

        $member->password = $this->encoderFactory
            ->getEncoder(FrontendUser::class)
            ->encodePassword($member->password, null)
        ;

        $this->createNewUser($member);

        return $this->redirectToRoute('registration_confirm');
    }

    private function resendActivationMail(MemberModel $member): void
    {
        if (!$member->disable) {
            return;
        }

        $this->dispatchMessage(new AccountResendActivation((int) $member->id));

        $this->addFlash('confirmation', new TranslatableMessage('MSCresendActivation', [], 'contao_default'));
    }

    private function createNewUser(MemberModel $member): void
    {
        $member->username = $member->email;
        $member->tstamp = $member->dateAdded = time();
        $member->login = true;
        $member->groups = serialize(['2']);
        $member->disable = true;

        $member->save();

        $this->dispatchMessage(new AccountCreated((int) $member->id));
    }

    private function findTargetPath(Request $request): string
    {
        // If the form was submitted and the credentials were wrong, take the target
        // path from the submitted data as otherwise it would take the current page
        if ($request->isMethod('POST') && $request->request->has('_target_path')) {
            $targetPath = base64_decode((string) $request->request->get('_target_path'), true);
        } elseif ($request->query->has('redirect')) {
            // We cannot use $request->getUri() here as we want to work with the original URI (no query string reordering)
            if ($this->get('uri_signer')->check($request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo().(null !== ($qs = $request->server->get('QUERY_STRING')) ? '?'.$qs : ''))) {
                $targetPath = $request->query->get('redirect');
            }
        }

        $exception = $this->authenticationUtils->getLastAuthenticationError();
        $authorizationChecker = $this->get('security.authorization_checker');

        if ($exception instanceof LockedException) {
            $message = sprintf($GLOBALS['TL_LANG']['ERR']['accountLocked'], $exception->getLockedMinutes());
        } elseif ($exception instanceof InvalidTwoFactorCodeException) {
            $message = $GLOBALS['TL_LANG']['ERR']['invalidTwoFactor'];
        } elseif ($exception instanceof AuthenticationException) {
            $message = $GLOBALS['TL_LANG']['ERR']['invalidLogin'];
        }

        if ($twoFactorEnabled = $authorizationChecker->isGranted('IS_AUTHENTICATED_2FA_IN_PROGRESS')) {
            // Dispatch 2FA form event to prepare 2FA providers
            $token = $this->get('security.token_storage')->getToken();
            $event = new TwoFactorAuthenticationEvent($request, $token);
            $this->get('event_dispatcher')->dispatch($event, TwoFactorAuthenticationEvents::FORM);
        }

        if (null === ($targetPath ?? null)) {
            $targetPath = $request->getSchemeAndHttpHost().$request->getRequestUri();
        }

        return $targetPath ?? '';
    }
}
