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

namespace Ferienpass\CoreBundle\Messenger;

use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;

/**
 * A message handler may return this object which holds notifications sent inside the message handler.
 */
class NotificationHandlerResult
{
    /**
     * Messages sent by this message handler.
     *
     * @var NotificationContext[]
     */
    private array $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
