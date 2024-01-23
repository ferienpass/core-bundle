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

namespace Ferienpass\CoreBundle\Message;

use Ferienpass\CoreBundle\Entity\Attendance;

class RemindAttendance implements LoggableMessageInterface
{
    public function __construct(private readonly int $attendanceId)
    {
    }

    public function getAttendanceId(): int
    {
        return $this->attendanceId;
    }

    public function getRelated(): array
    {
        return [
            Attendance::class => $this->attendanceId,
        ];
    }
}
