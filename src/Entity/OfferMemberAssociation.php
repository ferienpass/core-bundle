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
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;

#[ORM\Entity]
class OfferMemberAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\Column(name: 'member_id', type: 'integer', options: ['unsigned' => true])]
    private int $member;

    #[ORM\ManyToOne(targetEntity: OfferInterface::class, inversedBy: 'memberAssociations')]
    #[ORM\JoinColumn(name: 'offer_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private OfferInterface $offer;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    public function getMember(): int
    {
        return $this->member;
    }

    public function getOffer(): OfferInterface
    {
        return $this->offer;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
