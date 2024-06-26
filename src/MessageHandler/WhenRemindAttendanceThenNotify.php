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

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\MessengerLog;
use Ferienpass\CoreBundle\Message\RemindAttendance;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenRemindAttendanceThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly AttendanceRepository $attendances)
    {
    }

    public function __invoke(RemindAttendance $message, MessengerLog $log): void
    {
        /** @var Attendance $attendance */
        $attendance = $this->attendances->find($message->getAttendanceId());
        if (null === $attendance || '' === $email = (string) $attendance->getParticipant()?->getEmail()) {
            return;
        }

        $notification = $this->notifier->remindAttendance($attendance, $attendance->getOffer()->getEdition());
        if (null === $notification) {
            return;
        }

        $this->notifier->send($notification->belongsTo($log), new Recipient($email, (string) $attendance->getParticipant()->getMobile()));
    }
}
