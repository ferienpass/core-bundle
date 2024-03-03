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
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\MessageLog;
use Ferienpass\CoreBundle\Message\AttendanceStatusChanged;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class WhenAttendanceWithdrawnThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly AttendanceRepository $repository, private readonly ManagerRegistry $doctrine, private readonly TranslatorInterface $translator)
    {
    }

    public function __invoke(AttendanceStatusChanged $message, MessageLog $log): void
    {
        if (!$message->shallNotify()) {
            return;
        }

        /** @var Attendance $attendance */
        $attendance = $this->repository->find($message->getAttendanceId());
        if (null === $attendance || !$attendance->isWithdrawn()) {
            return;
        }

        $notification = $this->notifier->attendanceWithdrawn($attendance, $attendance->getOffer()->getEdition());
        if (null === $notification || '' === $email = (string) $attendance->getParticipant()?->getEmail()) {
            return;
        }

        $this->notifier->send($notification->belongsTo($log), new Recipient($email, (string) $attendance->getParticipant()->getMobile()));
    }
}
