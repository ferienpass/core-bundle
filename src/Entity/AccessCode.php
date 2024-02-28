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

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['strategy_id', 'code'])]
class AccessCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: AccessCodeStrategy::class, inversedBy: 'codes')]
    #[ORM\JoinColumn(name: 'strategy_id', referencedColumnName: 'id')]
    private AccessCodeStrategy $strategy;

    #[ORM\Column(type: 'string')]
    private string $code;

    #[ORM\JoinTable(name: 'AccessCodeToParticipant')]
    #[ORM\JoinColumn(name: 'code_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'accessCodes')]
    private Collection $participants;

    public function __construct(AccessCodeStrategy $strategy, string $code)
    {
        $this->strategy = $strategy;
        $this->code = $code;

        $this->createdAt = new \DateTimeImmutable();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getStrategy(): AccessCodeStrategy
    {
        return $this->strategy;
    }

    // TODO make immutable
    public function setCode(?string $code): void
    {
        $this->code = (string) $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant)
    {
        $this->participants[] = $participant;
    }

    public function removeParticipant(Participant $participant)
    {
        $this->participants->removeElement($participant);
    }
}
