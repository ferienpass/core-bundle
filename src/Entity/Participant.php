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
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ferienpass\CoreBundle\Repository\ParticipantRepository;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[Groups('admin_list')]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id')]
    private ?User $user;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    #[Groups(['admin_list', 'notification'])]
    private string $firstname;

    #[ORM\Column(type: 'string', length: 255, nullable: true, options: ['default' => ''])]
    #[Groups(['admin_list', 'notification'])]
    private ?string $lastname = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['admin_list', 'notification'])]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[PhoneNumber(defaultRegion: 'DE')]
    #[Groups('admin_list')]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[PhoneNumber(type: PhoneNumber::MOBILE, defaultRegion: 'DE')]
    private ?string $mobile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    #[Groups('admin_list')]
    private bool $discounted = false;

    #[ORM\ManyToMany(targetEntity: AccessCode::class, mappedBy: 'participants')]
    private Collection $accessCodes;

    /**
     * @psalm-var Collection<int, Attendance>
     */
    #[ORM\OneToMany(mappedBy: 'participant', targetEntity: Attendance::class, cascade: ['remove'])]
    private Collection $attendances;

    #[ORM\OneToMany(mappedBy: 'participant', targetEntity: ParticipantLog::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $activity;

    public function __construct(User $user = null)
    {
        $this->user = $user;
        $this->createdAt = new \DateTimeImmutable();
        $this->attendances = new ArrayCollection();
        $this->activity = new ArrayCollection();
        $this->accessCodes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstname(): string
    {
        return $this->firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function getName(): string
    {
        return sprintf('%s %s', $this->getFirstname(), $this->getLastname());
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function getOwnPhone(): ?string
    {
        return $this->phone;
    }

    public function getPhone(): ?string
    {
        if ($this->phone) {
            return $this->phone;
        }

        if (null === $user = $this->getUser()) {
            return null;
        }

        return $user->getPhone();
    }

    public function getOwnMobile(): ?string
    {
        return $this->mobile;
    }

    #[Groups(['admin_list', 'notification'])]
    public function getMobile(): ?string
    {
        if ($this->mobile) {
            return $this->mobile;
        }

        if (null === $user = $this->getUser()) {
            return null;
        }

        return $user->getMobile();
    }

    public function getOwnEmail(): ?string
    {
        return $this->email;
    }

    #[Groups(['admin_list', 'notification'])]
    public function getEmail(): ?string
    {
        if ($this->email) {
            return $this->email;
        }

        if (null === $user = $this->getUser()) {
            return null;
        }

        return $user->getEmail();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public function getAge(\DateTimeInterface $atDate = null): ?int
    {
        if (null === $this->dateOfBirth) {
            return null;
        }

        return $this->dateOfBirth->diff($atDate ?? new \DateTimeImmutable())->y;
    }

    /**
     * @return Collection|Attendance[]
     *
     * @psalm-return Collection<int, Attendance>
     */
    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    /**
     * @return Collection|Attendance[]
     *
     * @psalm-return Collection<int, Attendance>
     */
    public function getAttendancesNotWithdrawn(Edition $edition = null): Collection
    {
        return $this->attendances->filter(fn (Attendance $attendance) => Attendance::STATUS_WITHDRAWN !== $attendance->getStatus() && (null === $edition || $edition === $attendance->getOffer()->getEdition()));
    }

    public function getAttendancesConfirmed(): Collection
    {
        return $this->getAttendancesByStatus(Attendance::STATUS_CONFIRMED);
    }

    public function getAttendancesWaitlisted(): Collection
    {
        return $this->getAttendancesByStatus(Attendance::STATUS_WAITLISTED);
    }

    public function getAttendancesWaiting(): Collection
    {
        return $this->getAttendancesByStatus(Attendance::STATUS_WAITING);
    }

    public function getAttendancesErrored(): Collection
    {
        return $this->getAttendancesByStatus(Attendance::STATUS_ERROR);
    }

    /**
     * @return ArrayCollection|Attendance[]
     *
     * @psalm-return ArrayCollection<int, Attendance>
     */
    public function getAttendancesByStatus(string $status): Collection
    {
        if (!\in_array($status, [Attendance::STATUS_CONFIRMED, Attendance::STATUS_WAITLISTED, Attendance::STATUS_WAITING, Attendance::STATUS_WITHDRAWN, Attendance::STATUS_ERROR], true)) {
            throw new \InvalidArgumentException("Status \"$status\" is unknown to the application.");
        }

        return $this->attendances->filter(fn (Attendance $attendance) => $status === $attendance->getStatus());
    }

    #[Groups('admin_list')]
    public function getAttendancesConfirmedCount(): int
    {
        return $this->getAttendancesConfirmed()->count();
    }

    #[Groups('admin_list')]
    public function getAttendancesWaitlistedCount(): int
    {
        return $this->getAttendancesWaitlisted()->count();
    }

    #[Groups('admin_list')]
    public function getAttendancesWaitingCount(): int
    {
        return $this->getAttendancesWaiting()->count();
    }

    #[Groups('admin_list')]
    public function getAttendancesErroredCount(): int
    {
        return $this->getAttendancesErrored()->count();
    }

    /**
     * @return ArrayCollection|Attendance[]
     *
     * @psalm-return ArrayCollection<int, Attendance>
     */
    public function getAttendancesPaid(): Collection
    {
        return $this->attendances->filter(fn (Attendance $attendance) => $attendance->isPaid());
    }

    #[Groups('admin_list')]
    public function getAttendancesPaidCount(): int
    {
        return $this->getAttendancesPaid()->count();
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

    public function getActivity(): Collection
    {
        return $this->activity;
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

    public function setOwnPhone(?string $phone): void
    {
        $this->setPhone($phone);
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function setOwnMobile(?string $mobile): void
    {
        $this->setMobile($mobile);
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setOwnEmail(?string $email): void
    {
        $this->setEmail($email);
    }

    public function isDiscounted(): bool
    {
        return $this->discounted;
    }

    public function setDiscounted(bool $discounted): void
    {
        $this->discounted = $discounted;
    }

    #[Groups('admin_list')]
    public function hasUnpaidAttendances(): bool
    {
        return !$this->getAttendancesConfirmed()->filter(fn (Attendance $a) => !$a->isPaid())->isEmpty();
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getAccessCodes(): Collection
    {
        return $this->accessCodes;
    }

    public function addAccessCode(?AccessCode $code)
    {
        if (null === $code) {
            return;
        }

        if ($this->accessCodes->contains($code)) {
            return;
        }

        $this->accessCodes->add($code);
        $code->addParticipant($this);
    }

    public function removeAccessCode(AccessCode $code)
    {
        if ($this->accessCodes->contains($code)) {
            $this->accessCodes->removeElement($code);
            $code->removeParticipant($this);
        }
    }
}
