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

use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public function __construct(private readonly Security $security, private readonly UserRepository $repository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, ['view', 'edit', 'delete', 'password'], true)) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var User $account */
        $account = $subject;

        return match ($attribute) {
            'view' => $this->canView($account, $user),
            'edit' => $this->canEdit($account, $user),
            'delete' => $this->canDelete($account, $user),
            'password' => $this->canChangePassword($account, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(User $account, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canEdit(User $account, User $user): bool
    {
        if ($user === $account) {
            return true;
        }

        if ($account->isAdmin()) {
            return $this->security->isGranted('ROLE_SUPER_ADMIN');
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(User $account, User $user): bool
    {
        if ($account->isAdmin()) {
            return $this->security->isGranted('ROLE_SUPER_ADMIN');
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canChangePassword(User $account, User $user): bool
    {
        if ($account->isAdmin()) {
            return $this->security->isGranted('ROLE_SUPER_ADMIN');
        }

        return $this->security->isGranted('ROLE_ADMIN');
    }
}
