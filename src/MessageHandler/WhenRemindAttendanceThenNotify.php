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
use Ferienpass\CoreBundle\Message\RemindAttendance;
use Ferienpass\CoreBundle\Notifier;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenRemindAttendanceThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly AttendanceRepository $repository)
    {
    }

    public function __invoke(RemindAttendance $message): void
    {
        /** @var Attendance $attendance */
        $attendance = $this->repository->find($message->getAttendanceId());
        if (null === $attendance || '' === $email = (string) $attendance->getParticipant()?->getEmail()) {
            return;
        }

        $notification = $this->notifier->remindAttendance($attendance);
        if (null === $notification) {
            return;
        }

        $this->notifier->send($notification, new Recipient($email));
    }
}
