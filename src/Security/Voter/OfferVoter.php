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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OfferVoter extends Voter
{
    private AttendanceRepository $attendanceRepository;
    private HostRepository $hostRepository;

    public function __construct(AttendanceRepository $attendanceRepository, HostRepository $hostRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->hostRepository = $hostRepository;
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

        switch ($attribute) {
            case 'view':
                return $this->canView($offer, $user);

            case 'edit':
                return $this->canEdit($offer, $user);

            case 'create':
                return $this->canCreate($offer);

            case 'copy':
                return $this->canCopy($offer, $user);

            case 'delete':
                return $this->canDelete($offer, $user);

            case 'cancel':
                return $this->canCancel($offer, $user);

            case 'freeze':
                return $this->canFreeze($offer, $user);

            case 'relaunch':
                return $this->canRelaunch($offer, $user);

            case 'participants.view':
                return $this->canViewParticipants($offer, $user);

            case 'participants.add':
                return $this->canAddParticipants($offer, $user);

            case 'participants.reject':
                return $this->canRejectParticipants($offer, $user);

            case 'participants.confirm':
                return $this->canConfirmParticipants($offer, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Offer $offer, FrontendUser $user): bool
    {
        $userHosts = $this->hostRepository->findByMemberId((int) $user->id);
        $userHostIds = array_map(fn (Host $host) => $host->getId(), $userHosts);

        return $offer->getHosts()->filter(fn (Host $host) => \in_array($host->getId(), $userHostIds, false))->count() > 0;
    }

    private function canEdit(Offer $offer, FrontendUser $user): bool
    {
        if (false === $this->canView($offer, $user)) {
            return false;
        }

        return (null === ($edition = $offer->getEdition())) || $edition->isEditableForHosts();
    }

    private function canCreate(Offer $offer): bool
    {
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

        return $offer->getAttendances()->isEmpty()
            && (null === ($edition = $offer->getEdition()) || $edition->isEditableForHosts());
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
