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
use Ferienpass\CoreBundle\Repository\SentMessageRepository;

#[ORM\Entity(repositoryClass: SentMessageRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['email' => SentEmail::class, 'sms' => SentSms::class])]
class SentMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MessengerLog::class, inversedBy: 'notifications')]
    #[ORM\JoinColumn(name: 'log_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?MessengerLog $logEntry = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isEmail(): bool
    {
        return SentEmail::class === static::class;
    }

    public function isSms(): bool
    {
        return SentSms::class === static::class;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setLogEntry(?MessengerLog $logEntry): void
    {
        $this->logEntry = $logEntry;
    }

    public function getLogEntry(): ?MessengerLog
    {
        return $this->logEntry;
    }
}
