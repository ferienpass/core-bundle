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

use Ferienpass\CoreBundle\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class MailingNotification extends AbstractNotification implements EmailNotificationInterface
{
    private array $context = [];

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function context(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public static function getAvailableTokens(): array
    {
        return array_merge(parent::getAvailableTokens(), ['user', 'token']);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $email = (new NotificationEmail('mailing'))
            ->to($recipient->getEmail())
            ->subject($this->getSubject())
            ->content($this->getContent())
            ->context($this->getContext())
        ;

        if (null !== $this->getReplyTo()) {
            $email->replyTo($this->getReplyTo());
        }

        return new EmailMessage($email);
    }
}
