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
use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EditionVoter extends Voter
{
    public function __construct(private Security $security, private EditionRepository $editionRepository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, ['view', 'edit', 'stats'], true)) {
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
        if (!$user instanceof FrontendUser) {
            return false;
        }

        /** @var Edition $edition */
        $edition = $subject;

        return match ($attribute) {
            'view', 'stats' => $this->canView($edition, $user),
            'edit' => $this->canEdit($edition, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(Edition $host, FrontendUser $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return false;
    }

    private function canEdit(Edition $host, FrontendUser $user): bool
    {
        return $this->canView($host, $user);
    }
}
