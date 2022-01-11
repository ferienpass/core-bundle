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

namespace Ferienpass\CoreBundle\Facade;

use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystems;
use Ferienpass\CoreBundle\ApplicationSystem\LotApplicationSystem;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\OfferDate;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Message\AttendanceCreated;
use Ferienpass\CoreBundle\Message\AttendanceStatusChanged;
use Ferienpass\CoreBundle\Message\ParticipantListChanged;
use Symfony\Component\Messenger\MessageBusInterface;

class AttendanceFacade
{
    public function __construct(private MessageBusInterface $messageBus, private ManagerRegistry $doctrine, private ApplicationSystems $applicationSystems)
    {
    }

    /**
     * Preview an attendance status without persisting the attendance.
     */
    public function preview(Offer $offer, Participant $participant): Attendance
    {
        $attendance = new Attendance($offer, $participant);

        $applicationSystem = $this->applicationSystems->findApplicationSystem($offer);
        if (null !== $applicationSystem) {
            $applicationSystem->assignStatus($attendance);
        }

        return $attendance;
    }

    /**
     * Create (or update) an attendance for a given participant and offer.
     *
     * If an explicit status is given, no application procedure is used.
     * Setting an explicit attendance status shall only be possible for admins.
     *
     * @throws \RuntimeException in case no unambiguous application system is applicable
     */
    public function create(Offer $offer, Participant $participant, string $status = null, bool $notify = true): void
    {
        $attendance = $this->findOrCreateAttendance($offer, $participant);
        if (null === $attendance) {
            return;
        }

        $this->setStatus($attendance, $status);

        $this->doctrine->getManager()->flush();

        $this->messageBus->dispatch(new AttendanceCreated($attendance->getId(), $notify));
        $this->messageBus->dispatch(new ParticipantListChanged($offer->getId()));
    }

    public function delete(Attendance $attendance): void
    {
        $offer = $attendance->getOffer();
        $now = new \DateTimeImmutable();

        /** @var OfferDate $date */
        if (($date = $offer->getDates()->first()) && $date->getBegin() <= $now) {
            throw new \LogicException('Cannot withdraw application after begin of event');
        }

        if (null !== $offer->getApplicationDeadline() && $now >= $offer->getApplicationDeadline()) {
            throw new \LogicException('Cannot withdraw application after application deadline');
        }

        // When Lot application system, do not keep track of withdrawn participants
        $applicationSystem = $this->applicationSystems->findApplicationSystem($offer);
        if ($applicationSystem instanceof LotApplicationSystem) {
            $this->deleteAttendance($attendance);

            return;
        }

        $this->withdrawAttendance($attendance);
    }

    private function findOrCreateAttendance(Offer $offer, Participant $participant): ?Attendance
    {
        $attendance = $this->doctrine->getRepository(Attendance::class)->findOneBy(['offer' => $offer, 'participant' => $participant]);

        if (null === $attendance) {
            $attendance = new Attendance($offer, $participant);

            $this->doctrine->getManager()->persist($attendance);

            return $attendance;
        }

        // When an attendance already exists for this user, this action is immutable
        // But if existing attendance was withdrawn from the user, allow re-sign-ups
        if (!$attendance->isWithdrawn()) {
            return null;
        }

        // Reset status to allow re-assignment from current application system
        $attendance->setStatus(null);

        return $attendance;
    }

    private function setStatus(Attendance $attendance, ?string $status): void
    {
        if (null !== $status) {
            $attendance->setStatus($status);

            return;
        }

        $offer = $attendance->getOffer();

        $applicationSystem = $this->applicationSystems->findApplicationSystem($offer);
        if (null === $applicationSystem) {
            throw new \RuntimeException('Cannot create attendance without an applicable application procedure');
        }

        $applicationSystem->assignStatus($attendance);
    }

    private function deleteAttendance(Attendance $attendance): void
    {
        $em = $this->doctrine->getManager();
        $em->remove($attendance);
        $em->flush();

        $this->messageBus->dispatch(new ParticipantListChanged($attendance->getOffer()->getId()));
    }

    private function withdrawAttendance(Attendance $attendance): void
    {
        $oldStatus = $attendance->getStatus();
        $attendance->setStatus('withdrawn');

        $this->doctrine->getManager()->flush();

        $this->messageBus->dispatch(new AttendanceStatusChanged($attendance->getId(), $oldStatus, $attendance->getStatus()));
        $this->messageBus->dispatch(new ParticipantListChanged($attendance->getOffer()->getId()));
    }
}
