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
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\OfferDate;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Ferienpass\CoreBundle\Repository\HostRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OfferVoter extends Voter
{
    public function __construct(private Security $security, private AttendanceRepository $attendanceRepository, private HostRepository $hostRepository)
    {
    }

    protected function supports($attribute, $subject): bool
    {
        $operations = [
            'view',
            'create',
            'edit',
            'copy',
            'delete',
            'cancel',
            'freeze',
            'relaunch',
            'participants.view',
            'participants.add',
            'participants.reject',
            'participants.confirm',
        ];

        return $subject instanceof Offer && \in_array($attribute, $operations, true);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof FrontendUser) {
            return false;
        }

        /** @var Offer $offer */
        $offer = $subject;

        return match ($attribute) {
            'view' => $this->canView($offer, $user),
            'edit' => $this->canEdit($offer, $user),
            'create' => $this->canCreate($offer),
            'copy' => $this->canCopy($offer, $user),
            'delete' => $this->canDelete($offer, $user),
            'cancel' => $this->canCancel($offer, $user),
            'freeze' => $this->canFreeze($offer, $user),
            'relaunch' => $this->canRelaunch($offer, $user),
            'participants.view' => $this->canViewParticipants($offer, $user),
            'participants.add' => $this->canAddParticipants($offer, $user),
            'participants.reject' => $this->canRejectParticipants($offer, $user),
            'participants.confirm' => $this->canConfirmParticipants($offer, $user),
            default => throw new \LogicException('This code should not be reached!'),
        };
    }

    private function canView(Offer $offer, FrontendUser $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $userHosts = $this->hostRepository->findByMemberId((int) $user->id);
        $userHostIds = array_map(fn (Host $host) => $host->getId(), $userHosts);

        return $offer->getHosts()->filter(fn (Host $host) => \in_array($host->getId(), $userHostIds, false))->count() > 0;
    }

    private function canEdit(Offer $offer, FrontendUser $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (false === $this->canView($offer, $user)) {
            return false;
        }

        return (null === ($edition = $offer->getEdition())) || $edition->isEditableForHosts();
    }

    private function canCreate(Offer $offer): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (null === $edition = $offer->getEdition()) {
            return true;
        }

        return !$edition->getActiveTasks('host_editing_stage')->isEmpty();
    }

    private function canCopy(Offer $offer, FrontendUser $user): bool
    {
        return $this->canCreate($offer) && $this->canView($offer, $user);
    }

    private function canDelete(Offer $offer, FrontendUser $user): bool
    {
        if (false === $this->canView($offer, $user)) {
            return false;
        }

        if (!$offer->getAttendances()->isEmpty()) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return null === ($edition = $offer->getEdition()) || $edition->isEditableForHosts();
    }

    private function canCancel(Offer $offer, FrontendUser $user): bool
    {
        if (false === $this->canView($offer, $user)) {
            return false;
        }

        if ($this->offerIsImmutable($offer)) {
            return false;
        }

        return true;
    }

    private function canFreeze(Offer $offer, FrontendUser $user): bool
    {
        if (false === $this->canView($offer, $user)) {
            return false;
        }

        if ($this->offerIsImmutable($offer)) {
            return false;
        }

        $attendances = $this->attendanceRepository->findBy(['offer' => $offer]);

        return 0 === \count($attendances);
    }

    private function canRelaunch(Offer $offer, FrontendUser $user): bool
    {
        if (false === $this->canView($offer, $user)) {
            return false;
        }

        if ($this->offerIsImmutable($offer)) {
            return false;
        }

        return true;
    }

    private function canViewParticipants(Offer $offer, FrontendUser $user): bool
    {
        return $this->canView($offer, $user);
    }

    private function canRejectParticipants(Offer $offer, FrontendUser $user): bool
    {
        if ($this->offerIsImmutable($offer)) {
            return false;
        }

        return $this->canView($offer, $user);
    }

    private function canConfirmParticipants(Offer $offer, FrontendUser $user): bool
    {
        if ($this->offerIsImmutable($offer)) {
            return false;
        }

        return $this->canView($offer, $user);
    }

    private function canAddParticipants(Offer $offer, FrontendUser $user): bool
    {
        if ($this->offerIsImmutable($offer)) {
            return false;
        }

        return $this->canView($offer, $user);
    }

    private function offerIsImmutable(Offer $offer): bool
    {
        if ($offer->getDates()->isEmpty()) {
            return false;
        }

        /** @var OfferDate $date */
        $date = $offer->getDates()->first();

        return (new \DateTimeImmutable()) >= $date->getBegin();
    }
}
