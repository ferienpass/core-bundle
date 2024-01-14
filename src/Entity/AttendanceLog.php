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
class AttendanceLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'Attendance', inversedBy: 'activity')]
    #[ORM\JoinColumn(name: 'attendance_id', referencedColumnName: 'id')]
    private Attendance $attendance;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'Ferienpass\CoreBundle\Entity\User')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'string', length: 32)]
    private string $action;

    public function __construct(Attendance $attendance, string $action, User $user)
    {
        $this->attendance = $attendance;
        $this->action = $action;
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

    public function getAttendance(): Attendance
    {
        return $this->attendance;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
