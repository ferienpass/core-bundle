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

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/favicon.ico")
 */
final class FaviconIcoController
{
    private string $webDir;

    public function __construct(string $webDir)
    {
        $this->webDir = $webDir;
    }

    public function __invoke(): Response
    {
        return new BinaryFileResponse($this->webDir.'/bundles/ferienpasscore/favicon/favicon.ico');
    }
}
