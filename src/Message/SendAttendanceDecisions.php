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

/**
 * This message is a user-initiated request to notify all participants for their attendance status.
 */
class SendAttendanceDecisions implements LoggableMessageInterface
{
    public function __construct(private readonly int $editionId, private readonly array $attendanceIds)
    {
    }

    public function getEditionId(): int
    {
        return $this->editionId;
    }

    public function getAttendanceIds(): array
    {
        return $this->attendanceIds;
    }

    public function getRelated(): array
    {
        return [
            Attendance::class => $this->attendanceIds,
        ];
    }
}
