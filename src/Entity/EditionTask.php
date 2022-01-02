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

/**
 * @ORM\Entity
 */
class EditionTask
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Edition", inversedBy="tasks")
     * @ORM\JoinColumn(name="pid", referencedColumnName="id")
     */
    private Edition $edition;

    /**
     * @ORM\Column(name="tstamp", type="integer")
     */
    private int $timestamp;

    /**
     * @ORM\Column(type="integer")
     */
    private int $sorting;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private string $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $title = null;

    /**
     * @ORM\Column(name="max_applications", type="integer", nullable=true)
     */
    private ?int $maxApplications = null;

    /**
     * @ORM\Column(name="max_applications_day", type="integer", nullable=true)
     */
    private ?int $maxApplicationsDay = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private \DateTimeImmutable $periodBegin;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private \DateTimeImmutable $periodEnd;

    /**
     * @ORM\Column(name="hide_status", type="boolean", nullable=true)
     */
    private ?bool $hideStatus = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $description;

    /**
     * @ORM\Column(name="application_system", type="string", nullable=true)
     */
    private ?string $applicationSystem;

    /**
     * @ORM\Column(name="age_check", type="string", nullable=true)
     */
    private ?string $ageCheck = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Edition
     */
    public function getEdition()
    {
        return $this->edition;
    }

    /**
     * @param mixed $edition
     */
    public function setEdition($edition): void
    {
        $this->edition = $edition;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function setTimestamp(int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    public function getSorting(): int
    {
        return $this->sorting;
    }

    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getMaxApplications(): ?int
    {
        return $this->maxApplications;
    }

    public function setMaxApplications(int $maxApplications): void
    {
        $this->maxApplications = $maxApplications;
    }

    public function getMaxApplicationsDay(): ?int
    {
        return $this->maxApplicationsDay;
    }

    public function setMaxApplicationsDay(int $maxApplicationsDay): void
    {
        $this->maxApplicationsDay = $maxApplicationsDay;
    }

    public function getPeriodBegin(): ?\DateTimeImmutable
    {
        return $this->periodBegin;
    }

    public function setPeriodBegin(\DateTimeImmutable $periodBegin): void
    {
        $this->periodBegin = $periodBegin;
    }

    public function getPeriodEnd(): \DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function setPeriodEnd(\DateTimeImmutable $periodEnd): void
    {
        $this->periodEnd = $periodEnd;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getProgress(): int
    {
        if ($this->isCompleted()) {
            return 100;
        }

        if (false === $this->isActive()) {
            return 0;
        }

        if ('allocation' === $this->type) {
            $all = 0;
            $waiting = 0;
            foreach ($this->getEdition()->getOffers() as $offer) {
                $all += $offer->getAttendances()->count();
                $waiting += $offer->getAttendances()->filter(fn (Attendance $a) => $a->isWaiting())->count();
            }

            return $all > 0 ? (int) round(($waiting / $all) * 100) : 0;
        }

        $now = new \DateTimeImmutable();

        $duration = $this->getPeriodEnd()->diff($this->getPeriodBegin())->days;
        $elapsed = $now->diff($this->getPeriodBegin())->days;

        return (int) round(($elapsed / $duration) * 100);
    }

    /**
     * @return Collection|EditionTask[]
     * @psalm-return Collection<int, EditionTask>
     */
    public function getDependencies(): Collection
    {
        $allTasks = $this->edition->getTasks();

        switch (true) {
            case 'allocation' === $this->type:
                return $allTasks->filter(fn (EditionTask $t) => 'application_system' === $t->getType() && 'lot' === $t->getApplicationSystem());

            case 'application_system' === $this->type && 'firstcome' === $this->applicationSystem:
                return $allTasks->filter(fn (EditionTask $t) => 'allocation' === $t->getType());

            case 'publish_lists' === $this->type:
                return $allTasks->filter(fn (EditionTask $t) => 'allocation' === $t->getType());
        }

        return new ArrayCollection();
    }

    public function isCompleted(): bool
    {
        $now = new \DateTimeImmutable();
        if ($this->periodEnd > $now) {
            return false;
        }

        // Allocation tasks only are finished when all spots were assigned
        if ('allocation' === $this->type) {
            $waiting = 0;
            // FIXME
//            foreach ($this->getEdition()->getOffers() as $offer) {
//                $waiting += $offer->getAttendancesWaiting()->count();
//            }

            return 0 === $waiting;
        }

        foreach ($this->getDependencies() as $dependency) {
            if (false === $dependency->isCompleted()) {
                return false;
            }
        }

        return true;
    }

    public function isActive(): bool
    {
        $now = new \DateTimeImmutable();
        if ($this->periodBegin > $now || $this->periodEnd < $now) {
            return false;
        }

        foreach ($this->getDependencies() as $dependency) {
            if (false === $dependency->isCompleted()) {
                return false;
            }
        }

        return true;
    }

    public function isUpcoming(): bool
    {
        return !$this->isActive() && !$this->isCompleted();
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getApplicationSystem(): ?string
    {
        return $this->applicationSystem;
    }

    public function setApplicationSystem(string $applicationSystem): void
    {
        $this->applicationSystem = $applicationSystem;
    }

    public function isHideStatus(): bool
    {
        return (bool) $this->hideStatus;
    }

    public function setHideStatus(bool $hideStatus): void
    {
        $this->hideStatus = $hideStatus;
    }

    public function getAgeCheck(): ?string
    {
        return $this->ageCheck;
    }

    public function setAgeCheck(string $ageCheck): void
    {
        $this->ageCheck = $ageCheck;
    }
}
