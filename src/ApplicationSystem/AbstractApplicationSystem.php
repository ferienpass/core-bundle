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

namespace Ferienpass\CoreBundle\ApplicationSystem;

use Doctrine\Common\Collections\Criteria;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\EditionTask;

abstract class AbstractApplicationSystem implements ApplicationSystemInterface
{
    protected ?EditionTask $task = null;

    public function withTask(EditionTask $task): self
    {
        if (!$task->isAnApplicationSystem() || $this->getType() !== $task->getApplicationSystem()) {
            throw new \InvalidArgumentException(sprintf('Edition task must be an application system of type "%s"', $this->getType()));
        }

        $clone = clone $this;
        $clone->task = $task;

        return $clone;
    }

    public function getTask(): ?EditionTask
    {
        return $this->task;
    }

    public function assignStatus(Attendance $attendance): void
    {
        $this->setStatus($attendance);
        $this->applySorting($attendance);
    }

    protected function setStatus(Attendance $attendance): void
    {
        if (null !== $attendance->getStatus()) {
            return;
        }

        $attendance->setStatus('waiting', applicationSystem: $this);
    }

    protected function applySorting(Attendance $attendance): void
    {
        if ($attendance->getId()) {
            return;
        }

        $offer = $attendance->getOffer();
        $status = $attendance->getStatus();

        if (null !== $this->getTask()) {
            $attendance->setTask($this->getTask());
        }

        $lastAttendance = $status ? $offer->getAttendancesWithStatus($status)->filter(fn (Attendance $attendance) => $attendance->getSorting() > 0)->last() : null;
        /** @var Attendance|false $lastAttendanceParticipant */
        $lastAttendanceParticipant = $attendance->getParticipant()
            ?->getAttendancesWaiting()
            ?->matching(Criteria::create()->orderBy(['user_priority' => Criteria::ASC]))
            ?->last()
        ;

        $sorting = $lastAttendance?->getSorting() ?? 0;
        $sorting += 128;

        $priority = $lastAttendanceParticipant ? $lastAttendanceParticipant->getUserPriority() + 1 : 1;
        if ($maxApplications = $attendance->getTask()?->getMaxApplications()) {
            $priority = min($maxApplications + 1, $priority);
        }

        $attendance->setSorting($sorting);
        $attendance->setUserPriority($priority);
    }
}
