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
    public function __construct(private readonly int $attendance, private readonly bool $notify = true)
    {
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
