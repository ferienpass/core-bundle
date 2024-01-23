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

use Ferienpass\CoreBundle\Applications\UnconfirmedApplications;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Message\ConfirmApplications;
use Ferienpass\CoreBundle\Notifier;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenConfirmApplicationsThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly UnconfirmedApplications $unconfirmedApplications)
    {
    }

    public function __invoke(ConfirmApplications $message): void
    {
        foreach (array_merge([$this->unconfirmedApplications->getUninformedMembers(), $this->unconfirmedApplications->getUninformedParticipants()]) as $uninformedMember) {
            /** @var Attendance[] $attendances */
            $attendances = [];
            $notification = $this->notifier->attendanceDecisions($attendances);
            if (null === $notification || '' === $email = (string) $attendances[0]?->getParticipant()?->getEmail()) {
                continue;
            }

            $this->notifier->send($notification, new Recipient($email));
        }
    }
}
