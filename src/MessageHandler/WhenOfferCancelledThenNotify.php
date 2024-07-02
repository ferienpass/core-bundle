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

use Ferienpass\CoreBundle\Entity\MessengerLog;
use Ferienpass\CoreBundle\Entity\Offer\BaseOffer;
use Ferienpass\CoreBundle\Message\OfferCancelled;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\OfferRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenOfferCancelledThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly OfferRepositoryInterface $offers)
    {
    }

    public function __invoke(OfferCancelled $message, MessengerLog $log): void
    {
        /** @var BaseOffer $offer */
        $offer = $this->offers->find($message->getOfferId());
        if (null === $offer) {
            return;
        }

        foreach ($offer->getAttendancesConfirmed() as $attendance) {
            $notification = $this->notifier->offerCancelled($attendance, $attendance->getOffer()->getEdition());
            if (null === $notification || '' === $email = (string) $attendance->getParticipant()?->getEmail()) {
                continue;
            }

            $this->notifier->send($notification->belongsTo($log), new Recipient($email, (string) $attendance->getParticipant()->getMobile()));
        }
    }
}
