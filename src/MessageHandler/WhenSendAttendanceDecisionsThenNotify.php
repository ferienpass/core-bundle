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
use Ferienpass\CoreBundle\Message\SendAttendanceDecisions;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenSendAttendanceDecisionsThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly AttendanceRepository $repository)
    {
    }

    public function __invoke(SendAttendanceDecisions $message, MessengerLog $log): void
    {
        $decisions = [];
        /** @var Attendance $attendance */
        foreach ($this->repository->findBy(['id' => $message->getAttendanceIds()]) as $attendance) {
            $decisions[$attendance->getEmail()][] = $attendance;
        }

        /** @var Attendance $attendance */
        foreach ($decisions as $email => $attendances) {
            $notification = $this->notifier->attendanceDecisions($attendances);
            if (null === $notification || '' === $email) {
                continue;
            }

            $this->notifier->send($notification->belongsTo($log), new Recipient($email));
        }
    }
}
