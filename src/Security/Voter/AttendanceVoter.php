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

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AttendanceVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        if ('withdraw' !== $attribute) {
            return false;
        }

        if (!$subject instanceof Attendance) {
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

        /** @var Attendance $attendance */
        $attendance = $subject;

        if ('withdraw' === $attribute) {
            return $this->canWithdraw($attendance, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canWithdraw(Attendance $attendance, User $user): bool
    {
        $participant = $attendance->getParticipant();
        if (null === $participant) {
            return false;
        }

        if (null === $member = $participant->getUser()) {
            return false;
        }

        return (int) $member->getId() === (int) $user->getId();
    }
}
