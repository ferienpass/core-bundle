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
class OfferLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Offer::class, inversedBy: 'activity')]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id')]
    private Offer $offer;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $comment;

    public function __construct(Offer $offer, string $comment, User $user)
    {
        $this->offer = $offer;
        $this->comment = $comment;
        $this->user = $user;

        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getOffer(): Offer
    {
        return $this->offer;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
