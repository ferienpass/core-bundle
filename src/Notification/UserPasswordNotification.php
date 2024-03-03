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

use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Notifier\Message\EmailMessage;
use Ferienpass\CoreBundle\Notifier\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage as SymfonyEmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class UserPasswordNotification extends AbstractNotification implements NotificationInterface, EmailNotificationInterface
{
    use ActionUrlTrait;

    private string $token;
    private User $user;

    public static function getName(): string
    {
        return 'user_password';
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    public function token(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function user(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getContext(): array
    {
        return array_merge(parent::getContext(), [
            'user' => $this->user,
            'token' => $this->token,
        ]);
    }

    public static function getAvailableTokens(): array
    {
        return array_merge(parent::getAvailableTokens(), ['user', 'token']);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?SymfonyEmailMessage
    {
        return EmailMessage::fromFerienpassNotification($this, $recipient, function (NotificationEmail $email) {
            if (null !== $this->actionUrl) {
                $email->action('email.user_password.reset', $this->actionUrl);
            }
        });
    }
}
