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
use Ferienpass\CoreBundle\Message\AttendanceStatusChanged;
use Ferienpass\CoreBundle\Notifier;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenAttendanceConfirmedThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly AttendanceRepository $repository)
    {
    }

    public function __invoke(AttendanceStatusChanged $message): void
    {
        if (!$message->shallNotify()) {
            return;
        }

        /** @var Attendance $attendance */
        $attendance = $this->repository->find($message->getAttendanceId());
        if (null === $attendance || !$attendance->isConfirmed()) {
            return;
        }

        $notification = $this->notifier->attendanceChangedConfirmed($attendance);
        if (null === $notification || '' === $email = (string) $attendance->getParticipant()?->getEmail()) {
            return;
        }

        $this->notifier->send($notification, new Recipient($email, (string) $attendance->getParticipant()->getMobile()));
    }
}
