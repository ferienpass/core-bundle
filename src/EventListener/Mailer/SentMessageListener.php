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

namespace Ferienpass\CoreBundle\EventListener\Mailer;

use Ferienpass\CoreBundle\Entity\SentEmail;
use Ferienpass\CoreBundle\Notifier\Mime\NotificationEmail;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Event\SentMessageEvent;

#[AsEventListener]
class SentMessageListener
{
    public function __invoke(SentMessageEvent $event)
    {
        $message = $event->getMessage()->getOriginalMessage();
        if (!$message instanceof NotificationEmail) {
            return;
        }

        if (null === ($log = $message->getBelongsTo())) {
            return;
        }

        $log->addSentNotification(SentEmail::fromNotificationEmail($message, $event->getMessage()->getMessageId()));
    }
}
