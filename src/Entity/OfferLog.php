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
use Symfony\Component\Workflow\Transition;

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

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $transitionName = null;
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $transitionFrom = null;
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $transitionTo = null;

    public function __construct(Offer $offer, User $user, string $comment = null, Transition $transition = null)
    {
        $this->offer = $offer;
        $this->comment = $comment;
        $this->user = $user;

        if (null !== $transition) {
            $this->transitionName = $transition->getName();
            $this->transitionFrom = $transition->getFroms()[0];
            $this->transitionTo = $transition->getTos()[0];
        }

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

    public function isComment(): bool
    {
        return null !== $this->comment;
    }

    public function isTransition(): bool
    {
        return null !== $this->transitionName;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getTransition(): ?Transition
    {
        if (!$this->isTransition()) {
            return null;
        }

        return new Transition($this->transitionName, $this->transitionFrom, $this->transitionTo);
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
