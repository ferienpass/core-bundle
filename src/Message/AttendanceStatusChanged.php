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

class AttendanceStatusChanged implements LoggableMessageInterface
{
    public function __construct(private readonly int $attendanceId, private readonly ?string $oldStatus, private readonly string $newStatus, private $notify = true)
    {
    }

    public function getAttendanceId(): int
    {
        return $this->attendanceId;
    }

    public function getOldStatus(): ?string
    {
        return $this->oldStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function shallNotify(): bool
    {
        return $this->notify;
    }

    public function getRelated(): array
    {
        return [
            'Attendance' => $this->attendanceId,
        ];
    }
}
