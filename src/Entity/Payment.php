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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    final public const string STATUS_PAID = 'paid';
    final public const string STATUS_UNPAID = 'unpaid';
    final public const string STATUS_FAILED = 'failed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[Groups('admin_list')]
    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\OneToMany(targetEntity: PaymentItem::class, mappedBy: 'payment', cascade: ['persist'], orphanRemoval: true)]
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
    #[ORM\Column(type: 'text', length: 255, nullable: true)]
    private ?string $receiptNumber;

    #[Groups('admin_list')]
    #[ORM\Column(type: 'text', length: 64, nullable: true)]
    private ?string $status = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(name: 'pmpayment_txid', type: 'string', length: 64, nullable: true)]
    private ?string $pmPaymentTransactionId = null;

    public function __construct(string $receiptNumber = null, User $user = null, string $status = null)
    {
        $this->receiptNumber = $receiptNumber;
        $this->user = $user;

        if (null === $status) {
            $this->status = $status;
        }

        if (null !== $user) {
            $this->setBillingAddress(<<<EOF
{$user->getName()}
{$user->getStreet()}
{$user->getPostal()} {$user->getCity()}
{$user->getCountry()}
EOF
            );
            $this->setBillingEmail($user->getEmail());
        }

        $this->createdAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    /**
     * @param array<Attendance> $attendances
     */
    public static function fromAttendances(iterable $attendances, EventDispatcherInterface $dispatcher, string $receiptNumber = null, User $user = null): static
    {
        $self = new self($receiptNumber, $user);

        foreach ($attendances as $attendance) {
            $self->items->add(new PaymentItem($self, $attendance, $attendance->getOffer()->getFeePayable($attendance->getParticipant(), $dispatcher)));
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

    public function setReceiptNumber(string $receiptNumber): void
    {
        if (null !== $this->receiptNumber) {
            throw new \BadMethodCallException('Receipt number is already set.');
        }

        $this->receiptNumber = $receiptNumber;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function isPaid(): bool
    {
        return self::STATUS_PAID === $this->status;
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
    public function getUserEmail(): ?string
    {
        return $this->user?->getEmail();
    }

    public function calculateTotalAmount(): void
    {
        $this->totalAmount = array_sum(array_map(fn (PaymentItem $item) => $item->getAmount(), $this->items->toArray()));
    }

    public function getPmPaymentTransactionId(): ?string
    {
        return $this->pmPaymentTransactionId;
    }

    public function setPmPaymentTransactionId(?string $pmPaymentTransactionId): void
    {
        $this->pmPaymentTransactionId = $pmPaymentTransactionId;
    }

    public function isFinalized(): bool
    {
        return null !== $this->receiptNumber;
    }
}
