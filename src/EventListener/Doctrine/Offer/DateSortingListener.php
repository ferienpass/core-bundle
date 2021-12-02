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

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Ferienpass\CoreBundle\Entity\Offer;

/**
 * The field Offer.datesSorting is necessary for DC_Table to perform sorting on this field.
 */
class DateSortingListener
{
    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Offer) {
            return;
        }

        $dates = $entity->getDates();
        $date = $dates[0] ?? null;
        if (null === $date || null === $date->getBegin()) {
            return;
        }

        $entity->setDatesSorting($date->getBegin()->getTimestamp());
    }
}
