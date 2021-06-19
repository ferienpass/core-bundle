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

class RemindAttendance implements LoggableMessageInterface
{
    private int $attendance;

    public function __construct(int $attendance)
    {
        $this->attendance = $attendance;
    }

    public function getAttendance(): int
    {
        return $this->attendance;
    }

    public function getRelated(): array
    {
        return [
            'Attendance' => $this->attendance,
        ];
    }
}
