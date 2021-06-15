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

class AttendanceCreated implements LoggableMessageInterface
{
    private int $attendance;
    private bool $notify;

    public function __construct(int $attendance, bool $notify = true)
    {
        $this->attendance = $attendance;
        $this->notify = $notify;
    }

    public function getAttendance(): int
    {
        return $this->attendance;
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
