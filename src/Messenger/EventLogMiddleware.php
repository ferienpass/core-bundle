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

use Ferienpass\CoreBundle\Message\LoggableMessageInterface;
use Ferienpass\CoreBundle\Monolog\Context\MessageContext;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class EventLogMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null === $envelope->last(UniqueIdStamp::class)) {
            $envelope = $envelope->with(new UniqueIdStamp());
        }

        $envelope = $stack->next()->handle($envelope, $stack);
        $message = $envelope->getMessage();
        if (!$message instanceof LoggableMessageInterface) {
            return $envelope;
        }

        /** @var UniqueIdStamp $stamp */
        $stamp = $envelope->last(UniqueIdStamp::class);

        // Add the message context to the log.
        // The EventLogHandler will persist the message log in the database.
        $messageContext = new MessageContext($message);

        if ($envelope->last(HandledStamp::class)) {
            $this->logger->notice('Handled message', ['id' => $stamp->getUniqueId(), 'message' => $messageContext]);
        }

        return $envelope;
    }
}
