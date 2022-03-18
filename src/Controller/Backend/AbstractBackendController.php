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

namespace Ferienpass\CoreBundle\Controller\Backend;

use Contao\BackendUser;
use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InsufficientAuthenticationException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\RequestToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class AbstractBackendController extends AbstractController
{
    /**
     * Throws an exception if the user does not have access to the backend module.
     *
     * @throws AccessDeniedException
     */
    protected function denyAccessUnlessGranted2(Request $request): void
    {
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token instanceof AnonymousToken) {
            throw new InsufficientAuthenticationException('Not authenticated');
        }

        $authorizationChecker = $this->container->get('security.authorization_checker');
        if (!$authorizationChecker->isGranted('ROLE_USER') || !($user = $token->getUser()) instanceof BackendUser) {
            throw new AccessDeniedException('Access denied');
        }

        // Password change required
        if ($user->pwChange && !$authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            throw new RedirectResponseException('contao/password.php');
        }

        // Two-factor setup required
        if (!$user->useTwoFactor && $this->getParameter('contao.security.two_factor.enforce_backend') && 'security' !== $request->query->get('do')) {
            throw new RedirectResponseException($this->generateUrl('contao_backend', ['do' => 'security']));
        }

        // Front end redirect
        if ('feRedirect' === $request->query->get('do')) {
            trigger_deprecation('contao/core-bundle', '4.0', 'Using the "feRedirect" parameter has been deprecated and will no longer work in Contao 5.0. Use the "contao_backend_preview" route directly instead.');

            // Backend::redirectToFrontendPage(Input::get('page'), Input::get('article'));
        }

        // Backend user profile redirect
        if ($request->query->get('do') && ('edit' !== $request->query->get('act') && (int) $user->id !== $request->query->getInt('id'))) {
            $strUrl = $this->generateUrl('contao_backend', [
                'do' => 'login',
                'act' => 'edit',
                'id' => $user->id,
                'ref' => $request->attributes->get('_contao_referer_id'),
                'rt' => RequestToken::get(),
            ]);

            throw new RedirectResponseException($strUrl);
        }
    }
}
