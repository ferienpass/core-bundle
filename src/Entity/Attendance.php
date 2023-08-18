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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['offer_id', 'participant_id'])]
class Attendance
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_WAITLISTED = 'waitlisted';
    public const STATUS_WITHDRAWN = 'withdrawn';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_ERROR = 'error';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(name: 'tstamp', type: 'integer', options: ['unsigned' => true])]
    private int $timestamp;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $sorting = 0;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $status = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    private bool $paid = false;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $modifiedAt;

    #[ORM\ManyToOne(targetEntity: 'Ferienpass\CoreBundle\Entity\Offer', inversedBy: 'attendances')]
    #[ORM\JoinColumn(name: 'offer_id', referencedColumnName: 'id')]
    private Offer $offer;

    /**
     * The participant becomes NULL if personal data is erased but attendances are kept for data retention.
     */
    #[ORM\ManyToOne(targetEntity: 'Ferienpass\CoreBundle\Entity\Participant', inversedBy: 'attendances')]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id', nullable: true)]
    private ?Participant $participant = null;

    #[ORM\ManyToOne(targetEntity: 'EditionTask')]
    #[ORM\JoinColumn(name: 'task_id', referencedColumnName: 'id')]
    private ?EditionTask $task = null;

    #[ORM\OneToMany(mappedBy: 'attendance', targetEntity: AttendanceLog::class, cascade: ['persist', 'remove'])]
    private Collection $activity;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $userPriority = 0;

    /**
     * The participant age, retained for statistics.
     */
    #[ORM\Column(length: 3, type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $age = null;

    /**
     * The original participant id, retained for statistics.
     */
    #[ORM\Column(name: 'participant_id_original', type: 'integer', nullable: true, options: ['unsigned' => true])]
    private ?int $participantId = null;

    public function __construct(Offer $offer, Participant $participant, string $status = null)
    {
        $this->offer = $offer;
        $this->participant = $participant;

        $this->createdAt = new \DateTimeImmutable();
        $this->timestamp = time();
        $this->activity = new ArrayCollection();

        $this->setStatus($status);
    }

    public function __toString(): string
    {
        return (string) $this->getId();
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

    public function setStatus(?string $status, int $user = null): void
    {
        if (null !== $status && !\in_array($status, [self::STATUS_CONFIRMED, self::STATUS_WAITLISTED, self::STATUS_WITHDRAWN, self::STATUS_WAITING, self::STATUS_ERROR], true)) {
            throw new InvalidArgumentException('Invalid attendance status');
        }

        $this->status = $status;

        $this->setModifiedAt();

        if (null !== $user) {
        $activity = new AttendanceLog($this, $status, $user);
        $this->activity[] = $activity;
        }
    }

    public function getActivity(): Collection
    {
        return $this->activity;
    }

    public function setPaid($paid = true): void
    {
        $this->paid = $paid;
    }

    public function isPaid(): bool
    {
        return $this->paid;
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

    public function getUserPriority(): int
    {
        return $this->userPriority;
    }

    public function setUserPriority(int $userPriority): void
    {
        $this->userPriority = $userPriority;
    }

    /**
     * @Groups("docx_export")
     */
    public function getName(): string
    {
        return sprintf('%s %s', $this->participant?->getFirstname() ?? '', $this->participant?->getLastname() ?? '');
    }

    /**
     * @Groups("docx_export")
     */
    public function getPhone(): string
    {
        return $this->participant?->getPhone() ?? $this->participant?->getMember()?->phone ?? '';
    }

    /**
     * @Groups("docx_export")
     */
    public function getEmail(): string
    {
        return $this->participant?->getEmail() ?? $this->participant?->getMember()?->email ?? '';
    }

    /**
     * @Groups("docx_export")
     */
    public function getFee(): string
    {
        $fee = $this->offer->getFee();
        if (!$fee) {
            return '';
        }

        return sprintf('%s €', number_format($fee / 100, 2, ',', '.'));
    }
}
