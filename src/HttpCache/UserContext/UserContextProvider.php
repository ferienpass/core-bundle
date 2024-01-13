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

namespace Ferienpass\CoreBundle\HttpCache\UserContext;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\FrontendUser;
use FOS\HttpCache\UserContext\ContextProvider;
use FOS\HttpCache\UserContext\UserContext;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The UserContextProvider adds roles and groups to the UserContext for the hash generation.
 */
class UserContextProvider implements ContextProvider
{
    public function __construct(private readonly TokenStorageInterface $tokenStorage, private readonly ContaoFramework $framework, private readonly TokenChecker $tokenChecker)
    {
    }

    public function updateUserContext(UserContext $context): void
    {
        if (null === $this->tokenStorage) {
            throw new InvalidConfigurationException('The context hash URL must be under a firewall.');
        }

        $this->framework->initialize(true);

        if (null === $username = $this->tokenChecker->getFrontendUsername()) {
            $context->addParameter('authenticated', false);

            return;
        }

        $context->addParameter('authenticated', true);
        $context->addParameter('entity', FrontendUser::class);

        $user = $this->framework->getAdapter(FrontendUser::class)->loadUserByUsername($username);
        $groups = $user->groups;

        sort($groups);
        $context->addParameter('groups', $groups);
    }
}
