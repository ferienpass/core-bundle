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

trait OfferVariantsTrait
{
    #[ORM\OneToMany(mappedBy: 'variantBase', targetEntity: OfferInterface::class)]
    private Collection $variants;

    #[ORM\ManyToOne(targetEntity: OfferInterface::class, inversedBy: 'variants')]
    #[ORM\JoinColumn(name: 'varbase', referencedColumnName: 'id', onDelete: 'SET NULL')]
    private ?OfferInterface $variantBase = null;

    public function isVariantBase(): bool
    {
        return null === $this->variantBase;
    }

    public function isVariant(): bool
    {
        return !$this->isVariantBase();
    }

    public function hasVariants(): bool
    {
        return $this->isVariantBase() && \count($this->variants) > 0;
    }

    public function getVariants(bool $include = false): Collection
    {
        if ($this->isVariantBase()) {
            $variants = $this->variants->filter(fn (OfferInterface $v) => true);

            if ($include) {
                $variants->add($this);
            }

            return $variants;
        }

        $variants = $this->variantBase->getVariants(true);
        if ($include) {
            return $variants;
        }

        return $variants->filter(fn (OfferInterface $v) => $v->getId() !== $this->getId());
    }

    public function getVariantBase(): ?OfferInterface
    {
        return $this->variantBase;
    }

    public function setVariantBase(?OfferInterface $variantBase): void
    {
        if (null !== $variantBase && $variantBase->getVariantBase() && $variantBase->getVariantBase()->getId() !== $variantBase->getId()) {
            throw new \LogicException('Not allowed to set non-varbase as varbase');
        }

        $this->variantBase = $variantBase;
    }
}
