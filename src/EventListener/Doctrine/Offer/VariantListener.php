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

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Repository\OfferRepositoryInterface;

#[AsDoctrineListener('preUpdate')]
class VariantListener
{
    public static $processing = false;

    public function __construct(private readonly OfferRepositoryInterface $repository)
    {
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof OfferInterface) {
            return;
        }

        if ($entity->isVariant()) {
            return;
        }

        if (self::$processing) {
            return;
        }

        self::$processing = true;

        $entityManager = $args->getObjectManager();

        foreach ($entity->getVariants() as $variant) {
            $this->repository->updateVariant($variant);
        }

        $entityManager->flush();
    }
}
