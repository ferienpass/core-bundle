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
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Exception\AmbiguousHolidayTaskException;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity(repositoryClass: EditionRepository::class)]
class Edition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups('notification')]
    private ?string $name = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $alias = null;

    #[ORM\OneToMany(mappedBy: 'edition', targetEntity: EditionTask::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: 'edition', targetEntity: OfferInterface::class, cascade: ['remove'])]
    private Collection $offers;

    #[ORM\Column(type: 'boolean')]
    private bool $archived = false;

    #[ORM\Column(type: 'boolean')]
    private bool $hasAgreementLetter = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $agreementLetterText = null;

    #[ORM\ManyToMany(targetEntity: Host::class)]
    #[ORM\JoinTable(name: 'EditionToHost', )]
    #[ORM\JoinColumn(name: 'edition_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'host_id', referencedColumnName: 'id')]
    private Collection $hosts;

    #[ORM\Column(type: 'boolean')]
    private bool $hostsCanAssign = false;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->hosts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    /**
     * @return Collection|EditionTask[]
     *
     * @psalm-return Collection<int, EditionTask>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function setTasks(Collection $tasks): void
    {
        $this->tasks = $tasks;
    }

    public function addTask(EditionTask $editionTask): void
    {
        $editionTask->setEdition($this);

        $this->tasks->add($editionTask);
    }

    public function removeTask(EditionTask $editionTask): void
    {
        $this->tasks->removeElement($editionTask);
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    public function setArchived(bool $archived = true): void
    {
        $this->archived = $archived;
    }

    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function getHosts(): Collection
    {
        return $this->hosts;
    }

    public function hostsCanAssign(): bool
    {
        return $this->hostsCanAssign;
    }

    public function setHostsCanAssign(bool $hostsCanAssign): void
    {
        $this->hostsCanAssign = $hostsCanAssign;
    }

    public function hasAgreementLetter(): bool
    {
        return $this->hasAgreementLetter;
    }

    public function setHasAgreementLetter(bool $hasAgreementLetter): void
    {
        $this->hasAgreementLetter = $hasAgreementLetter;
    }

    public function getAgreementLetterText(): ?string
    {
        return $this->agreementLetterText;
    }

    public function setAgreementLetterText(?string $agreementLetterText): void
    {
        $this->agreementLetterText = $agreementLetterText;
    }

    public function getHoliday(): ?EditionTask
    {
        $tasks = $this->getTasks()->filter(static fn (EditionTask $element) => 'holiday' === $element->getType());

        if ($tasks->count() > 1) {
            throw new AmbiguousHolidayTaskException('More than one holiday found for the pass edition ID '.($this->getId() ?? 0));
        }

        if (false === $task = $tasks->current()) {
            return null;
        }

        return $task;
    }

    public function getHostEditingStages(): Collection
    {
        return $this->getTasks()->filter(fn (EditionTask $element) => 'host_editing_stage' === $element->getType());
    }

    /**
     * @return Collection<int, EditionTask>
     */
    public function getTasksOfType(string $type): Collection
    {
        return $this->getTasks()->filter(static fn (EditionTask $element) => $type === $element->getType());
    }

    /**
     * Get the host editing stages for this pass edition.
     */
    public function getCurrentHostEditingStage(): ?EditionTask
    {
        $now = new \DateTimeImmutable();
        $tasks = $this->getTasks()->filter(
            fn (EditionTask $element) => 'host_editing_stage' === $element->getType()
                && $now >= $element->getPeriodBegin()
                && $now < $element->getPeriodEnd()
        );

        if ($tasks->count() > 1) {
            throw new \LogicException('More than one host editing stage valid at the moment for pass edition ID'.($this->getId() ?? 0));
        }

        if ($tasks->isEmpty()) {
            return null;
        }

        /** @var EditionTask $task */
        $task = $tasks->current();

        return $task;
    }

    public function isParticipantListReleased(): bool
    {
        if ($this->tasks->filter(fn (EditionTask $element) => 'publish_lists' === $element->getType())->isEmpty()) {
            return true;
        }

        $time = new \DateTimeImmutable();
        $tasks = $this->tasks->filter(fn (EditionTask $element) => 'publish_lists' === $element->getType() && $time >= $element->getPeriodBegin() && $time < $element->getPeriodEnd());

        return !$tasks->isEmpty();
    }

    public function isOnline(): bool
    {
        if ($this->tasks->filter(fn (EditionTask $element) => 'show_offers' === $element->getType())->isEmpty()) {
            return true;
        }

        $time = new \DateTimeImmutable();
        $tasks = $this->tasks->filter(fn (EditionTask $element) => 'show_offers' === $element->getType() && $time >= $element->getPeriodBegin() && $time < $element->getPeriodEnd());

        return !$tasks->isEmpty();
    }

    /**
     * Check whether the host can edit the offers of this pass edition.
     */
    public function isEditableForHosts(): bool
    {
        $hasCurrentHostEditingStage = null !== $this->getCurrentHostEditingStage();
        $hasHostEditingStages = $this->getHostEditingStages()->count() > 0;

        return $hasCurrentHostEditingStage || !$hasHostEditingStages;
    }

    public function isCompleted(): bool
    {
        if (null === $this->getHoliday()) {
            return false;
        }

        return $this->getHoliday()->getPeriodEnd() < new \DateTimeImmutable();
    }

    /**
     * @return Collection<EditionTask>
     */
    public function getActiveTasks(string $taskName): Collection
    {
        $time = new \DateTimeImmutable();

        return $this->getTasks()->filter(
            fn (EditionTask $element) => $taskName === $element->getType()
                && $time >= $element->getPeriodBegin()
                && $time < $element->getPeriodEnd()
        );
    }

    public function getAccessCodeStrategy(): ?AccessCodeStrategy
    {
        foreach ($this->getActiveTasks('application_system') as $task) {
            if ($strategy = $task->getAccessCodeStrategy()) {
                return $strategy;
            }
        }

        return null;
    }

    public function generateAlias(SluggerInterface $slugger)
    {
        $this->alias = (string) $slugger->slug($this->getName() ?? uniqid())->lower();
    }
}
