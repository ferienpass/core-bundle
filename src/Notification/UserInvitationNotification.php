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
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class UserInvitationNotification extends AbstractNotification implements NotificationInterface, EmailNotificationInterface
{
    use ActionUrlTrait;

    private User $user;
    private Host $host;
    private string $email;

    public static function getName(): string
    {
        return 'user_invitation';
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    public function user(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function host(Host $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function email(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getContext(): array
    {
        return array_merge(parent::getContext(), [
            'user' => $this->user,
            'host' => $this->host,
            'email' => $this->email,
        ]);
    }

    public static function getAvailableTokens(): array
    {
        return array_merge(parent::getAvailableTokens(), ['user', 'host', 'email']);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?EmailMessage
    {
        $email = (new NotificationEmail(self::getName()))
            ->to($recipient->getEmail())
            ->subject($this->getSubject())
            ->content($this->getContent())
            ->context($this->getContext())
        ;

        if (null !== $this->getReplyTo()) {
            $email->replyTo($this->getReplyTo());
        }

        if (null !== $this->actionUrl) {
            $email->action('email.user_invitation.accept', $this->actionUrl);
        }

        return new EmailMessage($email);
    }
}
