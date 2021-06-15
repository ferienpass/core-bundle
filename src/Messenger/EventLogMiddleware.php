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

/**
 * When a message is of type {LoggableMessageInterface}, this middleware will log the message.
 */
class EventLogMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
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

        if ($handledStamp = $envelope->last(HandledStamp::class)) {
            $result = $handledStamp->getResult();

            // If message sent a notification, log it.
            if ($result instanceof NotificationHandlerResult) {
                foreach ($result->getResult() as $notificationContext) {
                    $this->logger->notice('Sent a notification in message', [
                        'id' => $stamp->getUniqueId(),
                        'message' => $messageContext,
                        'notification' => $notificationContext,
                    ]);
                }

                return $envelope;
            }

            $this->logger->notice('Handled message', ['id' => $stamp->getUniqueId(), 'message' => $messageContext]);
        }

        return $envelope;
    }
}
