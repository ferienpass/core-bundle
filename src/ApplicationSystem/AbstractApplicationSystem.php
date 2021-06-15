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

class AbstractApplicationSystem implements ApplicationSystemInterface
{
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

        $lastAttendance = $offer->getAttendancesWithStatus($status)->last();

        $sorting = $lastAttendance ? $lastAttendance->getSorting() : 0;
        $sorting += 128;

        $attendance->setSorting($sorting);

        if ($this instanceof TimedApplicationSystemInterface) {
            $attendance->setTask($this->getTask());
        }
    }
}
