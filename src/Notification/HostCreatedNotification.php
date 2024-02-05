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

namespace Ferienpass\CoreBundle\Notification;

use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class HostCreatedNotification extends Notification implements NotificationInterface, EmailNotificationInterface
{
    private Host $host;
    private User $user;

    public static function getName(): string
    {
        return 'host_created';
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    public function host(Host $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function user(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $email = (new NotificationEmail(self::getName()))
            ->to($recipient->getEmail())
            ->subject($this->getSubject())
            ->content($this->getContent())
            ->context([
                'host' => $this->host,
                'user' => $this->user,
            ])
        ;

        // TODO add CC header
        return new EmailMessage($email);
    }
}