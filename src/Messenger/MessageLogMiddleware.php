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

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Message\LoggableMessageInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandlerArgumentsStamp;

class MessageLogMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!($envelope->getMessage() instanceof LoggableMessageInterface)) {
            return $stack->next()->handle($envelope, $stack);
        }

        if (null === $envelope->last(LogStamp::class)) {
            $envelope = $envelope->with(new LogStamp($envelope->getMessage(), $this->entityManager));
        }

        $stamp = $envelope->last(LogStamp::class);

        $envelope = $envelope->with(new HandlerArgumentsStamp([
            $stamp->getLogEntity(),
        ]));

        return $stack->next()->handle($envelope, $stack);
    }
}
