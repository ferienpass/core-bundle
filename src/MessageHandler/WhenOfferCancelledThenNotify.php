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
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Message\OfferCancelled;
use Ferienpass\CoreBundle\Messenger\NotificationHandlerResult;
use Ferienpass\CoreBundle\Notifier;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class WhenOfferCancelledThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly OfferRepository $repository, private readonly ManagerRegistry $doctrine)
    {
    }

    public function __invoke(OfferCancelled $message): ?NotificationHandlerResult
    {
        /** @var Offer $offer */
        $offer = $this->repository->find($message->getOfferId());
        if (null === $offer) {
            return null;
        }

        foreach ($offer->getAttendances() as $attendance) {
            $notification = $this->notifier->offerCancelled($attendance);
            if (null === $notification || '' === $email = (string) $attendance->getParticipant()?->getEmail()) {
                continue;
            }

            $this->notifier->send($notification, new Recipient($email));
        }

        return null;
    }
}
