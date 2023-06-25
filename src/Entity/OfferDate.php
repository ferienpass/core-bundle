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

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class OfferDate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'Ferienpass\CoreBundle\Entity\Offer', inversedBy: 'dates')]
    #[ORM\JoinColumn(name: 'offer_id', referencedColumnName: 'id')]
    private Offer $offer;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $begin = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $end = null;

    public function __construct(Offer $offer = null)
    {
        if ($offer) {
            $this->offer = $offer;
        }
    }

    /**
     * Contao group widget cannot use constructor arguments.
     */
    public function withOffer(Offer $offer): self
    {
        $clone = clone $this;
        $clone->offer = $offer;

        return $clone;
    }

    public function getOffer(): Offer
    {
        return $this->offer;
    }

    public function getBegin(): ?\DateTimeInterface
    {
        return $this->begin;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setBegin(\DateTimeInterface $begin): void
    {
        $this->begin = $begin;
    }

    public function setEnd(\DateTimeInterface $end): void
    {
        $this->end = $end;
    }
}
