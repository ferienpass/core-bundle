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

namespace Ferienpass\CoreBundle\EventListener\Doctrine\Attendance;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\ParticipantLog;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\Transition;

#[AsEntityListener(event: Events::prePersist, entity: Attendance::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Attendance::class)]
class AttendanceLogListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function prePersist(Attendance $attendance): void
    {
        $attendance->getActivity()->add(new ParticipantLog(
            $attendance->getParticipant(),
            $this->security->getUser(),
            $attendance,
            transition: new Transition(Attendance::TRANSITION_CREATE, '', (string) $attendance->getStatus())
        ));
    }

    public function preUpdate(Attendance $attendance, PreUpdateEventArgs $eventArgs): void
    {
        if (!$eventArgs->hasChangedField('status') || null === ($status = $eventArgs->getNewValue('status'))) {
            return;
        }

        $transitionName = match ($status) {
            Attendance::STATUS_CONFIRMED => Attendance::TRANSITION_CONFIRM,
            Attendance::STATUS_WAITLISTED => Attendance::TRANSITION_WAITLIST,
            Attendance::STATUS_WITHDRAWN => Attendance::TRANSITION_WITHDRAW,
            Attendance::STATUS_WAITING => Attendance::TRANSITION_RESET,
        };

        if (null === $transitionName) {
            return;
        }

        $attendance->getActivity()->add(new ParticipantLog(
            $attendance->getParticipant(),
            $this->security->getUser(),
            $attendance,
            transition: new Transition($transitionName, (string) $eventArgs->getOldValue('status'), $status)
        ));
    }
}
