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

namespace Ferienpass\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ferienpass\CoreBundle\Notifier\Mime\NotificationEmail;
use Symfony\Component\Mime\Part\DataPart;

#[ORM\Entity]
class SentEmail extends SentMessage
{
    #[ORM\Column(type: 'json')]
    private array $emailData;

    #[ORM\Column(type: 'string')]
    private string $emailMessageId;

    private function __construct(string $emailMessageId)
    {
        parent::__construct();

        $this->emailMessageId = $emailMessageId;
    }

    public static function fromNotificationEmail(NotificationEmail $email, string $messageId)
    {
        $entity = new self($messageId);
        $entity->emailData = [
            'from' => ($email->getFrom()[0] ?? null)?->getAddress(),
            'to' => ($email->getTo()[0] ?? null)?->getAddress(),
            'replyTo' => ($email->getReplyTo()[0] ?? null)?->getAddress(),
            'subject' => $email->getSubject(),
            'type' => $email->getType(),
            'text' => (string) $email->getTextBody(),
            'html' => (string) $email->getHtmlBody(),
            'attachments' => array_map(fn (DataPart $attachment) => ['body' => null, 'filename' => $attachment->getFilename()], $email->getAttachments()),
        ];

        return $entity;
    }

    public function getMessageId(): string
    {
        return $this->emailMessageId;
    }

    public function getFrom(): ?string
    {
        return $this->emailData['from'] ?? null;
    }

    public function getTo(): ?string
    {
        return $this->emailData['to'] ?? null;
    }

    public function getReplyTo(): ?string
    {
        return $this->emailData['replyTo'] ?? null;
    }

    public function getSubject(): ?string
    {
        return $this->emailData['subject'] ?? null;
    }

    public function getText(): ?string
    {
        return $this->emailData['text'] ?? null;
    }

    public function getHtml(): ?string
    {
        return $this->emailData['html'] ?? null;
    }

    public function getAttachments(): ?array
    {
        return $this->emailData['attachments'] ?? null;
    }
}
