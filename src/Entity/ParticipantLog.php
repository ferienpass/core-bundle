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
use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystemInterface;
use Ferienpass\CoreBundle\Entity\Participant\ParticipantInterface;
use Symfony\Component\Workflow\Transition;

#[ORM\Entity]
class ParticipantLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ParticipantInterface::class, inversedBy: 'activity')]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?ParticipantInterface $participant;

    #[ORM\ManyToOne(targetEntity: Attendance::class, inversedBy: 'activity')]
    #[ORM\JoinColumn(name: 'attendance_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Attendance $attendance;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $comment;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $applicationSystem = null;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $transitionName = null;
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $transitionFrom = null;
    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    private ?string $transitionTo = null;

    public function __construct(?ParticipantInterface $participant, User $user = null, Attendance $attendance = null, ApplicationSystemInterface $applicationSystem = null, string $comment = null, Transition $transition = null)
    {
        $this->participant = $participant;
        $this->user = $user;
        $this->comment = $comment;
        $this->attendance = $attendance;

        if (null !== $attendance && $attendance->getParticipant() !== $participant) {
            throw new \InvalidArgumentException();
        }

        if (null !== $transition) {
            $this->transitionName = $transition->getName();
            $this->transitionFrom = $transition->getFroms()[0];
            $this->transitionTo = $transition->getTos()[0];
        }

        if (null !== $applicationSystem) {
            $this->applicationSystem = $applicationSystem->getType();
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

    public function getParticipant(): ParticipantInterface
    {
        return $this->participant;
    }

    public function getAttendance(): ?Attendance
    {
        return $this->attendance;
    }

    public function getApplicationSystem(): ?string
    {
        return $this->applicationSystem;
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

    public function getUser(): ?User
    {
        return $this->user;
    }
}
