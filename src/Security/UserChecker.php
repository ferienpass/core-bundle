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

use Ferienpass\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if ($user instanceof ContaoBackendUser) {
            $user = $user->getOriginalUser();
        }

        if (!$user instanceof User) {
            return;
        }

        $this->checkIfAccountIsDisabled($user);
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }

    private function checkIfAccountIsDisabled(User $user): void
    {
        if (!$user->isDisabled()) {
            return;
        }

        $ex = new DisabledException('The account has been disabled');
        $ex->setUser($user);

        throw $ex;
    }
}
