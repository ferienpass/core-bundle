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
class ParticipantLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'Participant', inversedBy: 'activity')]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id')]
    private Participant $participant;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'Ferienpass\CoreBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $comment;

    public function __construct(Participant $participant, string $comment, User $user)
    {
        $this->participant = $participant;
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

    public function getParticipant(): Participant
    {
        return $this->participant;
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
