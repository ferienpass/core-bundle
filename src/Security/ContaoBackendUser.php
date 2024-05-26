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

use Contao\BackendUser;
use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\User;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ContaoBackendUser extends BackendUser
{
    private EntityManagerInterface $em;
    private ?User $user = null;

    public static function fromFerienpassUser(User $user): BackendUser
    {
        $contaoUser = new static();

        $contaoUser->user = $user;
        $contaoUser->arrData = new class($user) implements \ArrayAccess {
            public function __construct(private User $user)
            {
            }

            public function offsetExists(mixed $offset): bool
            {
                if (\in_array($offset, ['admin', 'username', 'start', 'stop'], true)) {
                    return true;
                }

                $propertyAccessor = PropertyAccess::createPropertyAccessor();

                return $propertyAccessor->isReadable($this->user, $offset);
            }

            public function offsetGet(mixed $offset): mixed
            {
                $propertyAccessor = PropertyAccess::createPropertyAccessor();

                return match ($offset) {
                    // TODO this is repeated from ContaoUserProvier::loadUserByIdentifier
                    'admin' => \in_array('ROLE_SUPER_ADMIN', $this->user->getRoles(), true) || \in_array('ROLE_CMS_ADMIN', $this->user->getRoles(), true),

                    'username' => $this->user->getEmail(),
                    'start', 'stop' => '',
                    default => $propertyAccessor->getValue($this->user, $offset)
                };
            }

            public function offsetSet(mixed $offset, mixed $value): void
            {
                if (\in_array($offset, ['currentLogin'], true)) {
                    return;
                }

                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                $propertyAccessor->setValue($this->user, $offset, $value);
            }

            public function offsetUnset(mixed $offset): void
            {
                $propertyAccessor = PropertyAccess::createPropertyAccessor();
                $propertyAccessor->setValue($this->user, $offset, null);
            }
        };

        return $contaoUser;
    }

    public function getRoles(): array
    {
        return array_merge(['ROLE_CMS_ADMIN'], parent::getRoles());
    }

    public function getUserIdentifier(): string
    {
        return $this->getOriginalUser()->getUserIdentifier();
    }

    public function getOriginalUser(): User
    {
        if (null !== $this->user) {
            return $this->user;
        }

        $this->user = new User();

        return $this->user;
    }

    public function save()
    {
        // $this->em->flush();
    }
}
