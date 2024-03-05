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

/**
 * This message is a user-initiated request to notify all participants for their attendance status.
 */
class SendAttendanceDecisions implements LoggableMessageInterface
{
    public function __construct(private readonly int $editionId)
    {
    }

    public function getEditionId(): int
    {
        return $this->editionId;
    }

    public function getRelated(): array
    {
        return [
        ];
    }
}
