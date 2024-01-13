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
use Ferienpass\CoreBundle\Entity\User;

class ContaoBackendUser extends BackendUser
{
    public static function fromFerienpassUser(User $user): BackendUser
    {
        $contaoUser = new static();

        $contaoUser->id = $user->getId();
        $contaoUser->email = $user->getEmail();
        $contaoUser->username = $user->getUserIdentifier();
        $contaoUser->name = $user->getName();
        $contaoUser->password = $user->getPassword();
        $contaoUser->admin = \in_array('ROLE_ADMIN', $user->getRoles(), true);
        $contaoUser->filemounts = [];

        return $contaoUser;
    }
}
