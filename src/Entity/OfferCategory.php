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
use Symfony\Component\String\Slugger\SluggerInterface;

#[ORM\Entity]
class OfferCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToMany(targetEntity: OfferInterface::class, mappedBy: 'categories')]
    private Collection $offers;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    private ?string $alias = null;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function generateAlias(SluggerInterface $slugger)
    {
        if (!$this->id) {
            $this->alias = uniqid();

            return;
        }

        if (!$this->alias) {
            $this->alias = (string) $slugger->slug($this->getName())->lower();
        }
    }
}
