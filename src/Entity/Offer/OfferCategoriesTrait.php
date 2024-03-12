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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ferienpass\CoreBundle\Entity\OfferCategory;

trait OfferCategoriesTrait
{
    #[ORM\ManyToMany(targetEntity: OfferCategory::class, inversedBy: 'offers')]
    #[ORM\JoinTable(name: 'OfferCategoryAssociation', joinColumns: new ORM\JoinColumn('offer_id', 'id', onDelete: 'CASCADE'), inverseJoinColumns: new ORM\JoinColumn('category_id', 'id'))]
    private Collection $categories;

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    public function addCategory(OfferCategory $category): void
    {
        $this->categories->add($category);
    }

    public function removeCategory(OfferCategory $category): void
    {
        $this->categories->removeElement($category);
    }
}
