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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EditionVoter extends Voter
{
    protected function supports(string $attribute, $subject): bool
    {
        $operations = [
            'offer.create',
        ];

        return $subject instanceof Edition && \in_array($attribute, $operations, true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var Edition $edition */
        $edition = $subject;

        if ('offer.create' === $attribute) {
            return $this->canCreateOffer($edition);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateOffer(Edition $edition): bool
    {
        return !$edition->getActiveTasks('host_editing_stage')->isEmpty();
    }
}
