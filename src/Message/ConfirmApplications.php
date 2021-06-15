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
class ConfirmApplications implements LoggableMessageInterface
{
    /** @var int[] */
    private array $attendances;

    public function __construct(array $attendances)
    {
        $this->attendances = $attendances;
    }

    public function getRelated(): array
    {
        return [
            'Attendance' => $this->attendances,
        ];
    }
}
