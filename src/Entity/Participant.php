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

use Contao\MemberModel;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Participant
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $id;

    /**
     * @ORM\Column(name="tstamp", type="integer", options={"unsigned"=true})
     */
    private int $timestamp;

    /**
     * @ORM\Column(name="member_id", type="integer", options={"unsigned"=true}, nullable=true)
     */
    private ?int $member;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"default"=""})
     */
    private string $firstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"default"=""})
     */
    private string $lastname;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private ?\DateTimeInterface $dateOfBirth = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $phone = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $mobile = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $email = null;

    /**
     * @ORM\Column(type="boolean", options={"default"=0})
     */
    private bool $discounted = false;

    /**
     * @ORM\OneToMany(targetEntity="Ferienpass\CoreBundle\Entity\Attendance", mappedBy="participant")
     *
     * @psalm-var Collection<int, Attendance>
     */
    private Collection $attendances;

    public function __construct(int $memberId = null)
    {
        $this->member = $memberId;
        $this->timestamp = time();
        $this->attendances = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getMemberId(): ?int
    {
        return $this->member;
    }

    public function getMember(): ?MemberModel
    {
        return MemberModel::findByPk($this->member);
    }

    public function getAge(\DateTimeInterface $atDate = null): ?int
    {
        if (null === $this->dateOfBirth) {
            return null;
        }

        return $this->dateOfBirth->diff(($atDate ?? new \DateTimeImmutable()))->y;
    }

    /**
     * @return Collection|Attendance[]
     * @psalm-return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    /**
     * @return Collection|Attendance[]
     * @psalm-return Collection<int, Attendance>
     */
    public function getAttendancesNotWithdrawn(): Collection
    {
        return $this->attendances->filter(fn (Attendance $attendance) => Attendance::STATUS_WITHDRAWN !== $attendance->getStatus());
    }

    public function getLastAttendance(): ?Attendance
    {
        /** @var ArrayCollection $this->attendances */
        $criteria = Criteria::create()
            ->orderBy(['modifiedAt' => Criteria::DESC])
        ;

        $attendances = $this->attendances->matching($criteria);

        return $attendances->first() ?: null;
    }

    public function setFirstname(string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function setLastname(string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): void
    {
        $this->dateOfBirth = $dateOfBirth;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function isDiscounted(): bool
    {
        return $this->discounted;
    }

    public function setDiscounted(bool $discounted): void
    {
        $this->discounted = $discounted;
    }
}
