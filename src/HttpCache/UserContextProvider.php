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

namespace Ferienpass\CoreBundle\HttpCache;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\User;
use FOS\HttpCache\UserContext\ContextProvider;
use FOS\HttpCache\UserContext\UserContext;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserContextProvider implements ContextProvider
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly TokenChecker $tokenChecker)
    {
    }

    public function updateUserContext(UserContext $context): void
    {
        if (null === $this->tokenStorage) {
            throw new InvalidConfigurationException('The context hash URL must be under a firewall.');
        }

        if (null === $username = $this->tokenChecker->getFrontendUsername()) {
            $context->addParameter('authenticated', false);

            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof User) {
            return;
        }

        $context->addParameter('authenticated', true);

        // This way we do not need to add the role provider to the fos_http_cache config
        $roles = $user->getRoles();

        sort($roles);
        $context->addParameter('roles', $roles);

        if ($user->isHost() && !$user->isAdmin()) {
            $hosts = $user->getHosts()->map(fn (Host $host) => $host->getId())->toArray();

            sort($hosts);
            $context->addParameter('hosts', $hosts);
        }
    }
}
