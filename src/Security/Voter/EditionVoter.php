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

use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EditionVoter extends Voter
{
    public function __construct(private readonly Security $security, private readonly EditionRepository $editionRepository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, ['view', 'edit', 'stats', 'offer.create'], true)) {
            return false;
        }

        if (!$subject instanceof Edition) {
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

        /** @var Edition $edition */
        $edition = $subject;

        return match ($attribute) {
            'view', 'stats' => $this->canView($edition, $user),
            'edit' => $this->canEdit($edition, $user),
            'offer.create' => $this->canCreateOffer($edition),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(Edition $edition, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }

    private function canEdit(Edition $edition, User $user): bool
    {
        return $this->canView($edition, $user);
    }

    private function canCreateOffer(Edition $edition): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return !$edition->getActiveTasks('host_editing_stage')->isEmpty();
    }
}
