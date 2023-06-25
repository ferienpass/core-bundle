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

namespace Ferienpass\CoreBundle\Controller\Frontend;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/.well-known/security.txt')]
#[Route(path: '/security.txt')]
final class SecurityTxtController
{
    public function __invoke(): Response
    {
        $securityTxt = <<<'TXT'
Contact: mailto:security@henkenjohann.me
Encryption: https://keys.openpgp.org/vks/v1/by-fingerprint/B1B6E13C9D06FE9B5A81320BAF3640167D43F61C
Preferred-Languages: en, de

TXT;

        return new Response($securityTxt, Response::HTTP_OK, ['content-type' => 'text/plain']);
    }
}
