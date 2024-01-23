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

#[ORM\Entity(repositoryClass: 'Ferienpass\CoreBundle\Repository\EventLogRepository')]
class EventLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(type: 'string')]
    private string $uniqueId;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'message', type: 'text')]
    private string $message;

    #[ORM\OneToOne(targetEntity: Attendance::class)]
    #[ORM\JoinColumn(name: 'attendance_id', referencedColumnName: 'id')]
    private Attendance|null $attendance;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User|null $user;

    #[ORM\OneToOne(targetEntity: Offer::class)]
    #[ORM\JoinColumn(name: 'offer_id', referencedColumnName: 'id')]
    private Offer|null $offer;

    #[ORM\OneToOne(targetEntity: Payment::class)]
    #[ORM\JoinColumn(name: 'payment_id', referencedColumnName: 'id')]
    private Payment|null $payment;

    #[ORM\OneToMany(mappedBy: 'logEntry', targetEntity: 'Ferienpass\CoreBundle\Entity\NotificationLog', cascade: ['persist', 'remove'])]
    private Collection $notifications;

    public function __construct(string $uniqueId, string $message, Attendance $attendance = null, User $user = null, Offer $offer = null, Payment $payment = null, array $related = [])
    {
        $this->uniqueId = $uniqueId;
        $this->message = $message;
        $this->attendance = $attendance;
        $this->user = $user;
        $this->offer = $offer;
        $this->payment = $payment;
        $this->createdAt = new \DateTimeImmutable();
        $this->notifications = new ArrayCollection();

        foreach ($related as $item) {
            switch ($item::class) {
                case Attendance::class:
                    $this->attendance = $item;
                    break;
                case User::class:
                    $this->user = $item;
                    break;
                case Offer::class:
                    $this->offer = $item;
                    break;
                case Payment::class:
                    $this->payment = $item;
                    break;
            }
        }
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

    public function getAttendance(): ?Attendance
    {
        return $this->attendance;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }
}
