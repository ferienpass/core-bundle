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

use Doctrine\Common\Collections\Collection;
use Ferienpass\CoreBundle\Entity\Attendance;

class FirstComeApplicationSystem extends AbstractApplicationSystem
{
    public function getType(): string
    {
        return 'firstcome';
    }

    protected function setStatus(Attendance $attendance): void
    {
        $offer = $attendance->getOffer();
        $currentStatus = $attendance->getStatus();

        // Only assign attendance status for waitlisted or new attendances.
        // All other attendance statuses (confirmed, error, withdrawn,…) are persistent.
        if (null !== $currentStatus && !$attendance->isWaitlisted()) {
            return;
        }

        $max = $offer->getMaxParticipants();

        // Offers with no participant limit
        if (!$max) {
            $attendance->setStatus(Attendance::STATUS_CONFIRMED);

            return;
        }

        $attendances = $offer->getAttendancesConfirmedOrWaitlisted();
        $position = $this->calculatePosition($attendance, $attendances);

        // Existing participant and spots left
        if (null !== $position && $position < $max) {
            $attendance->setStatus(Attendance::STATUS_CONFIRMED);

            return;
        }

        // New participant and spots left
        if (null === $position && $attendances->count() < $max) {
            $attendance->setStatus(Attendance::STATUS_CONFIRMED);

            return;
        }

        $attendance->setStatus(Attendance::STATUS_WAITLISTED);
    }

    /**
     * Calculate the position of a participant in the participant list.
     * Returns NULL for new participants and the integer position (0 = first)
     * for existing participants on the list.
     */
    private function calculatePosition(Attendance $attendance, Collection $collection): ?int
    {
        if ($collection->isEmpty()) {
            return null;
        }

        $i = 0;
        foreach ($collection as $a) {
            if ($a->getParticipant() === $attendance->getParticipant()) {
                return $i;
            }

            ++$i;
        }

        return null;
    }
}
