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

namespace Ferienpass\CoreBundle\Twig\Extension;

use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RequestTokenExtension extends AbstractExtension
{
    private CsrfTokenManagerInterface $csrfTokenStorage;
    private string $csrfTokenName;

    public function __construct(CsrfTokenManagerInterface $csrfTokenStorage, string $csrfTokenName)
    {
        $this->csrfTokenStorage = $csrfTokenStorage;
        $this->csrfTokenName = $csrfTokenName;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('contao_request_token', [$this, 'requestToken']),
        ];
    }

    public function requestToken(): string
    {
        return $this->csrfTokenStorage->getToken($this->csrfTokenName)->getValue();
    }
}
