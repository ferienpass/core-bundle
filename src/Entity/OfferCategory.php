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
class OfferCategory
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
     * @ORM\ManyToMany(targetEntity="Ferienpass\CoreBundle\Entity\Offer", mappedBy="categories")
     */
    private Collection $offers;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private ?string $alias = null;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
    }

    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }
}
