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

namespace Ferienpass\CoreBundle\Controller\Backend\Api;

use Contao\CoreBundle\Exception\ResponseException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends SymfonyAbstractController
{
    protected function checkToken(): void
    {
        $token = $this->get('security.token_storage')->getToken();
        if (null === $token || $this->get('security.authentication.trust_resolver')->isAnonymous($token)) {
            throw new ResponseException(new Response('Not authenticated', Response::HTTP_UNAUTHORIZED));
        }

        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new ResponseException(new Response('Access Denied', Response::HTTP_UNAUTHORIZED));
        }
    }
}
