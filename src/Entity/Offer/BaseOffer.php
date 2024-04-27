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

namespace Ferienpass\CoreBundle\Entity\Offer;

use Contao\StringUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\OfferDate;
use Ferienpass\CoreBundle\Entity\OfferLog;
use Ferienpass\CoreBundle\Entity\OfferMemberAssociation;
use Ferienpass\CoreBundle\Entity\Participant\ParticipantInterface;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Payments\OfferFeeEvent;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[ORM\MappedSuperclass]
class BaseOffer implements OfferInterface
{
    use OfferEditionTrait;
    use OfferVariantsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[Groups('docx_export')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups(['notification', 'admin_list'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $modifiedAt;

    #[ORM\ManyToMany(targetEntity: Host::class, inversedBy: 'offers', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'HostOfferAssociation')]
    #[ORM\JoinColumn(name: 'offer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'host_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $hosts;

    /**
     * @psalm-var Collection<int, OfferMemberAssociation>
     */
    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: OfferMemberAssociation::class)]
    private Collection $memberAssociations;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    #[Groups(['docx_export', 'notification', 'admin_list'])]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    #[Groups('docx_export')]
    private ?string $alias = null;

    #[ORM\Column(type: 'string', length: 32, options: ['default' => OfferInterface::STATE_DRAFT])]
    private string $state;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['docx_export'])]
    private ?string $comment = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['docx_export', 'notification', 'admin_list'])]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups('docx_export')]
    private ?string $teaser = null;

    #[ORM\Column(type: 'binary_string', length: 16, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: 'binary_string', length: 16, nullable: true)]
    private ?string $agreementLetter = null;

    #[ORM\Column(type: 'binary_string', nullable: true)]
    private ?string $downloads = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $requiresAgreementLetter = null;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    #[Groups('docx_export')]
    private bool $requiresApplication = false;

    #[ORM\Column(type: 'boolean', options: ['default' => 0])]
    #[Groups('docx_export')]
    private bool $onlineApplication = false;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups('docx_export')]
    private ?\DateTimeInterface $applicationDeadline = null;

    private bool $saved = false;

    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    #[Assert\GreaterThan(0)]
    #[Groups('docx_export')]
    private ?int $minParticipants = null;

    #[ORM\Column(type: 'smallint', nullable: true, options: ['unsigned' => true])]
    #[Assert\GreaterThan(0)]
    #[Groups('docx_export')]
    private ?int $maxParticipants = null;

    #[ORM\Column(type: 'smallint', length: 2, nullable: true, options: ['unsigned' => true])]
    #[Assert\GreaterThan(0)]
    #[Groups('docx_export')]
    private ?int $minAge = null;

    #[ORM\Column(type: 'smallint', length: 2, nullable: true, options: ['unsigned' => true])]
    #[Assert\GreaterThan(0)]
    #[Groups('docx_export')]
    private ?int $maxAge = null;

    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero]
    #[Groups('docx_export')]
    private ?int $fee = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups('docx_export')]
    private ?string $meetingPoint = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups('docx_export')]
    private ?string $applyText = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[Groups('docx_export')]
    private ?User $contactUser = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups('docx_export')]
    private ?string $bring = null;

    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: OfferDate::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['begin' => 'ASC'])]
    private Collection $dates;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $accessibility = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $wheelchairAccessible = null;

    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: Attendance::class, cascade: ['remove'])]
    #[ORM\OrderBy(['status' => 'ASC', 'sorting' => 'ASC'])]
    private Collection $attendances;

    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: OfferLog::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $activity;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $applicationExtra = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->modifiedAt = new \DateTimeImmutable();
        $this->hosts = new ArrayCollection();
        $this->dates = new ArrayCollection();
        $this->variants = new ArrayCollection();
        $this->attendances = new ArrayCollection();
        $this->activity = new ArrayCollection();
        $this->state = OfferInterface::STATE_DRAFT;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addDate(OfferDate $offerDate): void
    {
        $this->dates->add($offerDate);
    }

    public function removeDate(OfferDate $offerDate): void
    {
        $this->dates->removeElement($offerDate);
    }

    public function getDates(): Collection
    {
        return $this->dates;
    }

    public function getHosts(): Collection
    {
        return $this->hosts;
    }

    public function addHost(Host $host): void
    {
        $this->hosts[] = $host;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function isPublished(): bool
    {
        return OfferInterface::STATE_PUBLISHED === $this->state;
    }

    public function requiresApplication(): bool
    {
        return $this->requiresApplication;
    }

    public function isOnlineApplication(): bool
    {
        return $this->onlineApplication;
    }

    public function isCancelled(): bool
    {
        return OfferInterface::STATE_CANCELLED === $this->state;
    }

    public function getMinParticipants(): ?int
    {
        return $this->minParticipants;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function getFee(): ?int
    {
        return $this->fee;
    }

    public function getMeetingPoint(): ?string
    {
        return $this->meetingPoint;
    }

    public function setName(?string $name): void
    {
        $this->name = (string) $name;
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setTeaser(?string $teaser): void
    {
        $this->teaser = $teaser;
    }

    public function setImage(?string $image): void
    {
        $this->image = $image;
    }

    public function setRequiresApplication(bool $requiresApplication): void
    {
        $this->requiresApplication = $requiresApplication;
    }

    public function setOnlineApplication(bool $onlineApplication): void
    {
        $this->onlineApplication = $onlineApplication;
    }

    public function setMinParticipants(?int $minParticipants): void
    {
        $this->minParticipants = $minParticipants;
    }

    public function setMaxParticipants(?int $maxParticipants): void
    {
        $this->maxParticipants = $maxParticipants;
    }

    public function setFee(?int $fee): void
    {
        $this->fee = $fee;
    }

    public function setMeetingPoint(?string $meetingPoint): void
    {
        $this->meetingPoint = $meetingPoint;
    }

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): void
    {
        $this->minAge = $minAge;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(?int $maxAge): void
    {
        $this->maxAge = $maxAge;
    }

    public function getDownloads(): ?array
    {
        if ('' === $this->downloads) {
            return null;
        }

        return StringUtil::deserialize($this->downloads);
    }

    public function getApplicationDeadline(): ?\DateTimeInterface
    {
        return $this->applicationDeadline;
    }

    public function setApplicationDeadline(?\DateTimeInterface $applicationDeadline): void
    {
        $this->applicationDeadline = $applicationDeadline;
    }

    public function getAttendances(): Collection
    {
        return $this->attendances;
    }

    public function getAttendancesNotWithdrawn(): Collection
    {
        return $this->getAttendances()->filter(fn (Attendance $attendance) => Attendance::STATUS_WITHDRAWN !== $attendance->getStatus());
    }

    public function getAttendancesConfirmed(): Collection
    {
        return $this->getAttendancesWithStatus(Attendance::STATUS_CONFIRMED);
    }

    public function getAttendancesWaiting(): Collection
    {
        return $this->getAttendancesWithStatus(Attendance::STATUS_WAITING);
    }

    public function getAttendancesWaitlisted(): Collection
    {
        return $this->getAttendancesWithStatus(Attendance::STATUS_WAITLISTED);
    }

    public function getAttendancesConfirmedOrWaitlisted(): Collection
    {
        return $this->getAttendancesWithStatuses([Attendance::STATUS_CONFIRMED, Attendance::STATUS_WAITLISTED]);
    }

    public function getAttendancesWithStatus(string $status): Collection
    {
        return $this->getAttendances()->filter(fn (Attendance $attendance) => $status === $attendance->getStatus());
    }

    public function getAttendancesWithStatuses(array $status): Collection
    {
        return $this->getAttendances()->filter(fn (Attendance $attendance) => \in_array($attendance->getStatus(), $status, true));
    }

    public function addAttendance(Attendance $attendance): void
    {
        $this->attendances->add($attendance);
    }

    public function getBring(): ?string
    {
        return $this->bring;
    }

    public function setBring(?string $bring): void
    {
        $this->bring = $bring;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getApplyText(): ?string
    {
        return $this->applyText;
    }

    public function setApplyText(?string $applyText): void
    {
        $this->applyText = $applyText;
    }

    public function getContactUser(): ?User
    {
        return $this->contactUser;
    }

    public function setContactUser(?User $contactUser): void
    {
        $this->contactUser = $contactUser;
    }

    public function getActivity(): Collection
    {
        return $this->activity;
    }

    public function getComments(): Collection
    {
        return $this->activity->filter(fn (OfferLog $l) => $l->isComment());
    }

    #[Groups(['docx_export', 'notification'])]
    public function getDate(): string
    {
        if (false === $date = $this->dates->first()) {
            return '';
        }

        return $date->getBegin()->format('d.m.Y H:i');
    }

    public function getAccessibility(): ?array
    {
        return $this->accessibility;
    }

    public function setAccessibility(?array $accessibility): void
    {
        $this->accessibility = $accessibility;
    }

    public function isWheelchairAccessible(): ?bool
    {
        return $this->wheelchairAccessible;
    }

    public function setWheelchairAccessible(?bool $wheelchairAccessible): void
    {
        $this->wheelchairAccessible = $wheelchairAccessible;
    }

    public function requiresAgreementLetter(): ?bool
    {
        return $this->requiresAgreementLetter;
    }

    public function setRequiresAgreementLetter(?bool $requiresAgreementLetter): void
    {
        $this->requiresAgreementLetter = $requiresAgreementLetter;
    }

    public function getAgreementLetter(): ?string
    {
        return $this->agreementLetter;
    }

    public function setAgreementLetter(?string $agreementLetter): void
    {
        $this->agreementLetter = $agreementLetter;
    }

    public function getMemberAssociations(): Collection
    {
        return $this->memberAssociations;
    }

    public function getApplicationExtra(): ?array
    {
        return $this->applicationExtra;
    }

    public function setApplicationExtra(?array $extra)
    {
        $this->applicationExtra = $extra;
    }

    public function isSaved(): bool
    {
        return $this->saved;
    }

    public function setSaved(bool $saved): void
    {
        $this->saved = $saved;
    }

    public function generateAlias(SluggerInterface $slugger)
    {
        if (!$this->id) {
            $this->alias = uniqid();

            return;
        }

        if (!$this->alias) {
            $this->alias = (string) $slugger->slug("{$this->getId()}-{$this->getName()}")->lower();
        }
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getModifiedAt(): \DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt = new \DateTimeImmutable()): void
    {
        $this->modifiedAt = $modifiedAt;
    }

    public function getFeePayable(ParticipantInterface $participant, EventDispatcherInterface $dispatcher): int
    {
        return $dispatcher->dispatch(new OfferFeeEvent($this->getFee(), $this, $participant))->getFee();
    }
}
