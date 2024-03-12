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

namespace Ferienpass\CoreBundle\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystems;
use Ferienpass\CoreBundle\Entity\Offer\BaseOffer;
use Ferienpass\CoreBundle\Entity\OfferDate;
use Ferienpass\CoreBundle\Message\AttendanceStatusChanged;
use Ferienpass\CoreBundle\Message\ParticipantListChanged;
use Ferienpass\CoreBundle\Repository\OfferRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class WhenParticipantListChangedThenReorder
{
    public function __construct(private readonly ApplicationSystems $applicationSystems, private readonly MessageBusInterface $messageBus, private readonly OfferRepositoryInterface $repository, private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(ParticipantListChanged $message): void
    {
        $now = new \DateTimeImmutable();
        $offerId = $message->getOfferId();

        /** @var BaseOffer $offer */
        $offer = $this->repository->find($offerId);
        /** @var OfferDate|false $date */
        $date = $offer->getDates()->first();

        // Do not update the attendances with the offer being in the past
        if (!$date || $now >= $date->getBegin()) {
            return;
        }

        // Only participants on waiting list can move up
        $attendances = $offer->getAttendancesWaitlisted();
        if ($attendances->isEmpty()) {
            return;
        }

        $applicationSystem = $this->applicationSystems->findApplicationSystem($offer);
        if (null === $applicationSystem) {
            return;
        }

        foreach ($attendances as $attendance) {
            $currentStatus = $attendance->getStatus();
            $applicationSystem->assignStatus($attendance);
            if ($attendance->getStatus() === $currentStatus) {
                continue;
            }

            $this->messageBus->dispatch(new AttendanceStatusChanged($attendance->getId(), $currentStatus, $attendance->getStatus()));
        }

        $this->em->flush();
    }
}
