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
use Ferienpass\CoreBundle\Facade\DecisionsFacade;
use Ferienpass\CoreBundle\Message\SendAttendanceDecisions;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenSendAttendanceDecisionsThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly DecisionsFacade $decisionsFacade, private readonly EditionRepository $editionRepository)
    {
    }

    public function __invoke(SendAttendanceDecisions $message, MessengerLog $log): void
    {
        $edition = $this->editionRepository->find($message->getEditionId());
        if (null === $edition) {
            return;
        }

        $attendances = $this->decisionsFacade->attendances($edition);

        $decisions = [];
        /** @var Attendance $attendance */
        foreach ($attendances as $attendance) {
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
