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

use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\User;

class HostCreated implements LoggableMessageInterface
{
    public function __construct(private readonly int $hostId, private readonly int $userId)
    {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getHostId(): int
    {
        return $this->hostId;
    }

    public function getRelated(): array
    {
        return [
            Host::class => $this->hostId,
            User::class => $this->userId,
        ];
    }
}
