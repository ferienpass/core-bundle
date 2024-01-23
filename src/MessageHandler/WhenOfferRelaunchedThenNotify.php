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

use Ferienpass\CoreBundle\Message\OfferRelaunched;
use Ferienpass\CoreBundle\Notifier;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenOfferRelaunchedThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly OfferRepository $repository)
    {
    }

    public function __invoke(OfferRelaunched $message): void
    {
        $offer = $this->repository->find($message->getOfferId());
        if (null === $offer) {
            return;
        }

        foreach ($offer->getAttendances() as $attendance) {
            $notification = $this->notifier->offerRelaunched($attendance);
            if (null === $notification || '' === $email = (string) $attendance->getParticipant()?->getEmail()) {
                continue;
            }

            // Todo if not reactive participants then discard attendances
            $this->notifier->send($notification, new Recipient($email));
        }
    }
}
