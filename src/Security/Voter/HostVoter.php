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
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Repository\HostRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class HostVoter extends Voter
{
    public function __construct(private HostRepository $hostRepository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        if (!\in_array($attribute, ['view', 'edit'], true)) {
            return false;
        }

        if (!$subject instanceof Host) {
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

        /** @var Host $host */
        $host = $subject;

        return match ($attribute) {
            'view' => $this->canView($host, $user),
            'edit' => $this->canEdit($host, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(Host $host, FrontendUser $user): bool
    {
        $hosts = $this->hostRepository->findByMemberId((int) $user->id);
        $hostIds = array_map(fn (Host $host) => $host->getId(), $hosts);

        return \in_array($host->getId(), $hostIds, false);
    }

    private function canEdit(Host $host, FrontendUser $user): bool
    {
        return $this->canView($host, $user);
    }
}
