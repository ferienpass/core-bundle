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
use Ferienpass\CoreBundle\Dto\Currency;
use Ferienpass\CoreBundle\Repository\PaymentRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    final public const STATUS_PAID = 'paid';
    final public const STATUS_UNPAID = 'unpaid';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[Groups('admin_list')]
    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\JoinTable(name: 'PaymentItemAssociation')]
    #[ORM\JoinColumn(name: 'payment_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'item_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: 'Ferienpass\CoreBundle\Entity\PaymentItem', cascade: ['persist'])]
    private Collection $items;

    #[ORM\Column(type: 'integer', options: ['unsigned' => false])]
    private int $totalAmount = 0;

    #[Groups('admin_list')]
    #[ORM\Column(type: 'text')]
    private ?string $billingAddress = null;

    #[Groups('admin_list')]
    #[ORM\Column(type: 'text', length: 255, nullable: true)]
    private ?string $billingEmail = null;

    #[Groups('admin_list')]
    #[ORM\Column(type: 'text', length: 255)]
    private ?string $receiptNumber;

    #[Groups('admin_list')]
    #[ORM\Column(type: 'text', length: 64)]
    private ?string $status = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private ?User $user = null;

    public function __construct(string $receiptNumber = null, User $user = null)
    {
        $this->receiptNumber = $receiptNumber;
        $this->user = $user;

        $this->status = self::STATUS_PAID;

        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    /**
     * @param array<Attendance> $attendances
     */
    public static function fromAttendances(iterable $attendances, string $receiptNumber = null): static
    {
        $self = new self($receiptNumber);

        foreach ($attendances as $attendance) {
            $self->items->add(new PaymentItem($attendance, $attendance->getOffer()->getFee() ?? 0));
        }

        $self->calculateTotalAmount();

        return $self;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    /** @return Collection<PaymentItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(PaymentItem $item): void
    {
        $this->items->add($item);

        $this->calculateTotalAmount();
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    #[Groups('admin_list')]
    public function getTotalAmountExcel(): Currency
    {
        return new Currency($this->getTotalAmount());
    }

    public function getBillingAddress(): ?string
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(string $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    public function getBillingEmail(): ?string
    {
        return $this->billingEmail;
    }

    public function setBillingEmail(?string $billingEmail): void
    {
        $this->billingEmail = $billingEmail;
    }

    public function getReceiptNumber(): ?string
    {
        return $this->receiptNumber;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    #[Groups('admin_list')]
    public function getUserEmail()
    {
        return $this->user?->getEmail();
    }

    public function calculateTotalAmount()
    {
        $this->totalAmount = array_sum(array_map(fn (PaymentItem $item) => $item->getAmount(), $this->items->toArray()));
    }
}
