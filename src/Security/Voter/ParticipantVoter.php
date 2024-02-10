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

use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ParticipantVoter extends Voter
{
    public function __construct(private readonly Security $security)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        $operations = [
            'view',
            'create',
            'edit',
            'delete',
        ];

        return $subject instanceof Participant && \in_array($attribute, $operations, true);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var Participant $participant */
        $participant = $subject;

        return match ($attribute) {
            'view' => $this->canView($participant, $user),
            'create' => $this->canCreate($participant),
            'edit' => $this->canEdit($participant, $user),
            'delete' => $this->canDelete($participant, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(Participant $participant, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canEdit(Participant $participant, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDelete(Participant $participant, User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canCreate(Participant $participant): bool
    {
        return $this->security->isGranted('ROLE_HOST') || $this->security->isGranted('ROLE_ADMIN');
    }
}
