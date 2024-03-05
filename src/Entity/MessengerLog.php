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
use Ferienpass\CoreBundle\Repository\MessengerLogRepository;

#[ORM\Entity(repositoryClass: MessengerLogRepository::class)]
class MessengerLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: 'message', type: 'string')]
    private string $message;

    #[ORM\ManyToMany(targetEntity: Attendance::class, inversedBy: 'messengerLogs')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'AttendanceMessengerLog')]
    private Collection $attendances;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'UserMessengerLog')]
    private Collection $users;

    #[ORM\ManyToMany(targetEntity: Offer::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'OfferMessengerLog')]
    private Collection $offers;

    #[ORM\ManyToMany(targetEntity: Payment::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'PaymentMessengerLog')]
    private Collection $payments;

    #[ORM\OneToMany(mappedBy: 'logEntry', targetEntity: SentMessage::class, cascade: ['persist', 'remove'])]
    private Collection $notifications;

    public function __construct(string $message, array $related = [])
    {
        $this->message = $message;
        $this->createdAt = new \DateTimeImmutable();
        $this->attendances = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->notifications = new ArrayCollection();

        foreach ($related as $item) {
            switch (true) {
                case $item instanceof Attendance:
                    $this->attendances[] = $item;
                    break;
                case $item instanceof User:
                    $this->users[] = $item;
                    break;
                case $item instanceof Offer:
                    $this->offers[] = $item;
                    break;
                case $item instanceof Payment:
                    $this->payments[] = $item;
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

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addSentNotification(SentMessage $notification): void
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setLogEntry($this);
        }
    }
}
