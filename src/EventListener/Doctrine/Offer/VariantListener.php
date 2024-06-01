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
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Repository\OfferRepositoryInterface;

#[AsEntityListener(event: Events::preUpdate, entity: OfferInterface::class)]
class VariantListener
{
    public static bool $processing = false;

    public function __construct(private readonly OfferRepositoryInterface $offers)
    {
    }

    public function preUpdate(OfferInterface $offer, LifecycleEventArgs $args): void
    {
        if ($offer->isVariant()) {
            return;
        }

        if (self::$processing) {
            return;
        }

        self::$processing = true;

        foreach ($offer->getVariants() as $variant) {
            $this->offers->updateVariant($variant);
        }

        $args->getObjectManager()->flush();
    }
}
