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
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * @ORM\Entity
 * @ORM\Table(uniqueConstraints={
 *   @ORM\UniqueConstraint(columns={"offer_id", "participant_id"})
 * })
 */
class Attendance
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_WAITLISTED = 'waitlisted';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_ERROR = 'error';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="tstamp", type="integer", options={"unsigned"=true})
     */
    private int $timestamp;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $sorting = 0;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private ?string $status = null;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private \DateTimeInterface $modifiedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Ferienpass\CoreBundle\Entity\Offer", inversedBy="attendances")
     * @ORM\JoinColumn(name="offer_id", referencedColumnName="id")
     */
    private Offer $offer;

    /**
     * The participant becomes NULL if personal data is erased but attendances are kept for data retention.
     *
     * @ORM\ManyToOne(targetEntity="Ferienpass\CoreBundle\Entity\Participant", inversedBy="attendances")
     * @ORM\JoinColumn(name="participant_id", referencedColumnName="id", nullable=true)
     */
    private ?Participant $participant = null;

    /**
     * @ORM\ManyToOne(targetEntity="EditionTask")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
     */
    private ?EditionTask $task = null;

    /**
     * The participant age, retained for statistics.
     *
     * @ORM\Column(length=3, type="integer", nullable=true, options={"unsigned"=true})
     */
    private ?int $age = null;

    public function __construct(Offer $offer, Participant $participant, string $status = null)
    {
        $this->offer = $offer;
        $this->participant = $participant;

        $this->createdAt = new \DateTimeImmutable();
        $this->timestamp = time();

        $this->setStatus($status);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): void
    {
        if (null !== $status && !\in_array($status, [self::STATUS_CONFIRMED, self::STATUS_WAITLISTED, self::STATUS_WITHDRAWN, self::STATUS_WAITING, self::STATUS_ERROR], true)) {
            throw new InvalidArgumentException('Invalid attendance status');
        }

        $this->status = $status;

        $this->setModifiedAt();
    }

    public function setConfirmed(): void
    {
        $this->setStatus(self::STATUS_CONFIRMED);
    }

    public function isConfirmed(): bool
    {
        return 'confirmed' === $this->status;
    }

    public function isWithdrawn(): bool
    {
        return 'withdrawn' === $this->status;
    }

    public function isWaitlisted(): bool
    {
        return 'waitlisted' === $this->status;
    }

    public function isWaiting(): bool
    {
        return 'waiting' === $this->status;
    }

    public function isErrored(): bool
    {
        return 'error' === $this->status;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getOffer(): Offer
    {
        return $this->offer;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function getTask(): ?EditionTask
    {
        return $this->task;
    }

    public function setTask(?EditionTask $task): void
    {
        $this->task = $task;
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
}
