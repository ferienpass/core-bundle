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

namespace Ferienpass\CoreBundle\Security\Voter;

use Contao\FrontendUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RoleVoter extends \Symfony\Component\Security\Core\Authorization\Voter\RoleVoter
{
    protected function extractRoles(TokenInterface $token)
    {
        $user = $token->getUser();
        if (!($user instanceof FrontendUser)) {
            return $token->getRoleNames();
        }

        $roles = $token->getRoleNames();

        if (\in_array($user->role, ['ROLE_ADMIN', 'ROLE_MEMBER', 'ROLE_HOST'], true)) {
            $roles = [$user->role];
        }

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            $roles[] = 'ROLE_HOST';
        }

        return array_unique($roles);
    }
}
