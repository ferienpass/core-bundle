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

namespace Ferienpass\CoreBundle\EventListener\Doctrine\Offer;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: OfferInterface::class)]
#[AsEntityListener(event: Events::preUpdate, entity: OfferInterface::class)]
class AliasListener
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    public function prePersist(OfferInterface $offer): void
    {
        $offer->generateAlias($this->slugger);
    }

    public function preUpdate(OfferInterface $offer): void
    {
        $offer->generateAlias($this->slugger);
    }
}
