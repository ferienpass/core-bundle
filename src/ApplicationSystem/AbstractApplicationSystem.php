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

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\EditionTask;

abstract class AbstractApplicationSystem implements ApplicationSystemInterface
{
    protected ?EditionTask $task = null;

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

        $attendance->setStatus('waiting');
    }

    protected function applySorting(Attendance $attendance): void
    {
        if ($attendance->getId()) {
            return;
        }

        $offer = $attendance->getOffer();
        $status = $attendance->getStatus();

        $lastAttendance = $status ? $offer->getAttendancesWithStatus($status)->last() : null;

        $sorting = $lastAttendance ? $lastAttendance->getSorting() : 0;
        $sorting += 128;

        $attendance->setSorting($sorting);

        if (null !== $this->getTask()) {
            $attendance->setTask($this->getTask());
        }
    }
}
