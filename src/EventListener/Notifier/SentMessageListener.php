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

namespace Ferienpass\CoreBundle\EventListener\Notifier;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Notifier\Event\SentMessageEvent;

#[AsEventListener]
class SentMessageListener
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(SentMessageEvent $event)
    {
        $message = $event->getMessage()->getOriginalMessage();

        //        $logEntry = new NotificationLog('', $message, $message->);
        //
        //        $this->entityManager->persist($logEntry);
        //
        //        $this->entityManager->flush();
    }
}
