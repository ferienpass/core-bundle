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

#[ORM\Entity]
class PaymentItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: Attendance::class, inversedBy: 'paymentItems')]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private Attendance|null $attendance;

    #[ORM\ManyToOne(targetEntity: Payment::class, inversedBy: 'items')]
    private Payment|null $payment;

    #[ORM\Column(type: 'integer', options: ['unsigned' => false])]
    private int $amount;

    public function __construct(Payment $payment, Attendance $attendance, int $amount)
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->payment = $payment;
        $this->attendance = $attendance;
        $this->amount = $amount;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayment(): Payment
    {
        return $this->payment;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAttendance(): ?Attendance
    {
        return $this->attendance;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    public function removeAttendanceAssociation(): void
    {
        $this->attendance = null;
    }
}
