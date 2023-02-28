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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Ferienpass\CoreBundle\Repository\EventLogRepository")
 */
class EventLog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $id;

    /**
     * @ORM\Column(type="string")
     */
    private string $uniqueId;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="message", type="text")
     */
    private string $message;

    /**
     * @ORM\OneToMany(targetEntity="Ferienpass\CoreBundle\Entity\EventLogRelated", mappedBy="logEntry", cascade={"persist", "remove"})
     */
    private Collection $related;

    /**
     * @ORM\OneToMany(targetEntity="Ferienpass\CoreBundle\Entity\NotificationLog", mappedBy="logEntry", cascade={"persist", "remove"})
     */
    private Collection $notifications;

    public function __construct(string $uniqueId, string $message)
    {
        $this->uniqueId = $uniqueId;
        $this->message = $message;
        $this->createdAt = new \DateTimeImmutable();
        $this->related = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRelated(): Collection
    {
        return $this->related;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function setRelated(ArrayCollection $related): void
    {
        $this->related = $related;
    }
}
