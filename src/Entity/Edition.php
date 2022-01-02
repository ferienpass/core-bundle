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
use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystemInterface;
use Ferienpass\CoreBundle\Exception\AmbiguousHolidayTaskException;
use Ferienpass\CoreBundle\Exception\MissingHolidayTaskException;

/**
 * @ORM\Entity(repositoryClass="Ferienpass\CoreBundle\Repository\EditionRepository")
 */
class Edition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer")
     */
    private int $tstamp;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $alias;

    /**
     * @ORM\OneToMany(targetEntity="Ferienpass\CoreBundle\Entity\EditionTask", mappedBy="edition")
     * @ORM\OrderBy({"sorting"="ASC"})
     *
     * @psalm-var Collection<int, EditionTask>
     */
    private Collection $tasks;

    /**
     * @ORM\OneToMany(targetEntity="Ferienpass\CoreBundle\Entity\Offer", mappedBy="edition")
     *
     * @psalm-var Collection<int, Offer>
     */
    private Collection $offers;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true}, nullable=true)
     */
    private ?int $listPage;

    /**
     * @var ApplicationSystemInterface
     */
    private $applicationSystem;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->offers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
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
     * @psalm-return Collection<int, EditionTask>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    /**
     * @param ArrayCollection $tasks
     */
    public function setTasks($tasks): void
    {
        $this->tasks = $tasks;
    }

    public function getListPage(): ?int
    {
        return $this->listPage;
    }

    public function setListPage(int $listPage): void
    {
        $this->listPage = $listPage;
    }

    /**
     * @return Collection|Offer[]
     * @psalm-return Collection<int, Offer>
     */
    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function getHoliday(): EditionTask
    {
        $tasks = $this->getTasks()->filter(static fn (EditionTask $element) => 'holiday' === $element->getType());

        if ($tasks->count() > 1) {
            throw new AmbiguousHolidayTaskException('More than one holiday found for the pass edition ID '.($this->getId() ?? 0));
        }

        if ($tasks->isEmpty()) {
            throw new MissingHolidayTaskException('No holiday found for pass edition ID '.($this->getId() ?? 0));
        }

        /** @var EditionTask $task */
        $task = $tasks->current();

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

    /**
     * Check whether the host can edit the offers of this pass edition.
     */
    public function isEditableForHosts(): bool
    {
        $hasCurrentHostEditingStage = null !== $this->getCurrentHostEditingStage();
        $hasHostEditingStages = $this->getHostEditingStages()->count() > 0;

        return $hasCurrentHostEditingStage || !$hasHostEditingStages;
    }

    public function getActiveTasks(string $taskName): Collection
    {
        $time = new \DateTimeImmutable();

        return $this->getTasks()->filter(
            fn (EditionTask $element) => $taskName === $element->getType()
                && $time >= $element->getPeriodBegin()
                && $time < $element->getPeriodEnd()
        );
    }
}
