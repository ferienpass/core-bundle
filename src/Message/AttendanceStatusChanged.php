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
    private int $attendance;
    private ?string $oldStatus;
    private string $newStatus;
    private bool $notify;

    public function __construct(int $attendance, ?string $oldStatus, string $newStatus, $notify = true)
    {
        $this->attendance = $attendance;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
        $this->notify = $notify;
    }

    public function getAttendance(): int
    {
        return $this->attendance;
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
            'Attendance' => $this->attendance,
        ];
    }
}
