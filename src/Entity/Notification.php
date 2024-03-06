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

#[ORM\Entity(repositoryClass: 'Ferienpass\CoreBundle\Repository\NotificationRepository')]
#[ORM\Table]
#[ORM\UniqueConstraint(columns: ['type', 'edition_id'])]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $modifiedAt;

    #[ORM\Column(type: 'string', length: 64)]
    private string $type;

    #[ORM\ManyToOne(targetEntity: Edition::class)]
    #[ORM\JoinColumn(name: 'edition_id', referencedColumnName: 'id')]
    private ?Edition $edition;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $emailReplyTo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $emailTo = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $emailSubject = null;

    #[ORM\Column(type: 'text', length: 65535, nullable: true)]
    private ?string $emailText = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $smsText = null;

    #[ORM\Column(type: 'boolean')]
    private bool $disable = false;

    public function __construct(string $type, Edition $edition = null)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->modifiedAt = new \DateTimeImmutable();

        $this->type = $type;
        $this->edition = $edition;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt = null): void
    {
        if (null === $modifiedAt) {
            $modifiedAt = new \DateTimeImmutable();
        }

        $this->modifiedAt = $modifiedAt;
    }

    public function getModifiedAt(): \DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEmailSubject(): ?string
    {
        return $this->emailSubject;
    }

    public function setEmailSubject(string $emailSubject): void
    {
        $this->emailSubject = $emailSubject;
    }

    public function getEmailReplyTo(): ?string
    {
        return $this->emailReplyTo;
    }

    public function getEmailTo(): ?string
    {
        return $this->emailTo;
    }

    public function setEmailReplyTo(?string $emailReplyTo): void
    {
        $this->emailReplyTo = $emailReplyTo;
    }

    public function getEmailText(): ?string
    {
        return $this->emailText;
    }

    public function setEmailText(string $emailText): void
    {
        $this->emailText = $emailText;
    }

    public function getSmsText(): ?string
    {
        return $this->smsText;
    }

    public function setSmsText(string $smsText): void
    {
        $this->smsText = $smsText;
    }

    public function isDisabled(): bool
    {
        return $this->disable;
    }

    public function setDisabled(bool $disable = true): void
    {
        $this->disable = $disable;
    }

    public function setEdition(Edition $edition): void
    {
        $this->edition = $edition;
    }

    public function getEdition(): ?Edition
    {
        return $this->edition;
    }
}
