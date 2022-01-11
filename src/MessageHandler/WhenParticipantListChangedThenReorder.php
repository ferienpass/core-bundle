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

use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystems;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\OfferDate;
use Ferienpass\CoreBundle\Message\AttendanceStatusChanged;
use Ferienpass\CoreBundle\Message\ParticipantListChanged;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class WhenParticipantListChangedThenReorder implements MessageHandlerInterface
{
    public function __construct(private ApplicationSystems $applicationSystems, private MessageBusInterface $messageBus, private ManagerRegistry $doctrine)
    {
    }

    public function __invoke(ParticipantListChanged $message)
    {
        $now = new \DateTimeImmutable();
        $offerId = $message->getOfferId();

        /** @var Offer $offer */
        $offer = $this->doctrine->getRepository(Offer::class)->find($offerId);
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

        $this->doctrine->getManager()->flush();
    }
}
