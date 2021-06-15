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
use NotificationCenter\Model\Message;
use NotificationCenter\Model\Notification;

/**
 * @ORM\Entity(repositoryClass="Ferienpass\CoreBundle\Repository\NotificationLogRepository")
 */
class NotificationLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="EventLog", inversedBy="notifications")
     * @ORM\JoinColumn(name="log_id", referencedColumnName="id")
     */
    private EventLog $logEntry;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $notification;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $message;

    /**
     * @ORM\Column(name="vars", type="json")
     */
    private array $variables;

    /**
     * @ORM\Column(type="string", length=6)
     */
    private string $language;

    public function __construct(EventLog $logEntry, int $notification, int $message, array $variables, string $language)
    {
        $this->logEntry = $logEntry;
        $this->notification = $notification;
        $this->message = $message;
        $this->variables = $variables;
        $this->language = $language;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getLogEntry(): EventLog
    {
        return $this->logEntry;
    }

    public function getNotification(): ?Notification
    {
        return Notification::findByPk($this->notification);
    }

    public function getMessage(): ?Message
    {
        return Message::findByPk($this->message);
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }
}
