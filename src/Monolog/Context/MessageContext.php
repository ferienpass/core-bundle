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

namespace Ferienpass\CoreBundle\Monolog\Context;

use Ferienpass\CoreBundle\Message\LoggableMessageInterface;

/**
 * Monolog context that is being added to log records resulting from loggable message.
 */
class MessageContext
{
    private LoggableMessageInterface $message;

    public function __construct(LoggableMessageInterface $message)
    {
        $this->message = $message;
    }

    public function getMessage(): LoggableMessageInterface
    {
        return $this->message;
    }
}
