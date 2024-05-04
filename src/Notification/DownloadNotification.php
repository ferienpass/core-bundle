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

use Ferienpass\CoreBundle\Notifier\Message\EmailMessage;
use Ferienpass\CoreBundle\Notifier\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage as SymfonyEmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class DownloadNotification extends AbstractNotification implements NotificationInterface, EmailNotificationInterface
{
    use ActionUrlTrait;

    private ?string $filename = null;

    public static function getName(): string
    {
        return 'download';
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    public function filename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getContext(): array
    {
        return array_merge(parent::getContext(), [
            'filename' => $this->filename,
        ]);
    }

    public static function getAvailableTokens(): array
    {
        return array_merge(parent::getAvailableTokens(), ['filename']);
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, string $transport = null): ?SymfonyEmailMessage
    {
        return EmailMessage::fromFerienpassNotification($this, $recipient, function (NotificationEmail $email) {
            if (null !== $this->actionUrl) {
                $email->action($this->filename ?? 'Download', $this->actionUrl);
            }
        });
    }
}
