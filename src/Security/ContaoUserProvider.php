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

namespace Ferienpass\CoreBundle\Security;

use Contao\CoreBundle\Framework\ContaoFramework;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\User as FerienpassUser;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class ContaoUserProvider extends EntityUserProvider
{
    private readonly ContaoFramework $contaoFramework;
    private readonly string $userClass;
    private readonly ManagerRegistry $registry;
    private readonly string $classOrAlias;

    public function __construct(ContaoFramework $contaoFramework, string $userClass, ManagerRegistry $registry, string $classOrAlias, string $property = null, string $managerName = null)
    {
        if (ContaoBackendUser::class !== $userClass && ContaoFrontendUser::class !== $userClass) {
            throw new \RuntimeException(sprintf('Unsupported class "%s".', $userClass));
        }

        parent::__construct($registry, $classOrAlias, $property, $managerName);

        $this->contaoFramework = $contaoFramework;
        $this->userClass = $userClass;
        $this->registry = $registry;
        $this->classOrAlias = $classOrAlias;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $this->contaoFramework->initialize();

        $user = parent::loadUserByIdentifier($identifier);

        if (!$user instanceof FerienpassUser || (!\in_array('ROLE_SUPER_ADMIN', $user->getRoles(), true) && !\in_array('ROLE_CMS_USER', $user->getRoles(), true))) {
            throw new UserNotFoundException(sprintf('Could not find user "%s"', $identifier));
        }

        return ContaoBackendUser::fromFerienpassUser($user);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof ContaoBackendUser && !$user instanceof ContaoFrontendUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_debug_type($user)));
        }

        $this->contaoFramework->initialize();

        $user = $this->registry->getManager()->getRepository($this->classOrAlias)->find($user->id);

        if (!$user instanceof FerienpassUser) {
            if (null === $user) {
                throw new UnsupportedUserException('Invalid user');
            }

            throw new UnsupportedUserException(sprintf('Unsupported class "%s".', $user::class));
        }

        return ContaoBackendUser::fromFerienpassUser($user);
    }

    public function supportsClass(string $class): bool
    {
        return $this->userClass === $class;
    }
}
