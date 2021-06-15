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

namespace Ferienpass\CoreBundle\Security\Authentication;

use Contao\CoreBundle\Security\Exception\LockedException;
use Scheb\TwoFactorBundle\Security\Authentication\Exception\InvalidTwoFactorCodeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof LockedException) {
            return new JsonResponse([
                'message' => sprintf($GLOBALS['TL_LANG']['ERR']['accountLocked'], $exception->getLockedMinutes()),
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if ($exception instanceof InvalidTwoFactorCodeException) {
            return new JsonResponse([
                'message' => $GLOBALS['TL_LANG']['ERR']['invalidTwoFactor'],
            ],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
    }
}
