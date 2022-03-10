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

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Offer;

/**
 * The field Offer.datesSorting is necessary for DC_Table to perform sorting on this field.
 */
class SortingFieldsListener
{
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Offer) {
            return;
        }

        if ($args->hasChangedField('datesSorting')) {
            $dates = $entity->getDates();
            $date = $dates[0] ?? null;

            $args->setNewValue('datesSorting', $date?->getBegin()?->getTimestamp());
        }

        if ($args->hasChangedField('hostsSorting') && false !== $host = $entity->getHosts()->first()) {
            /** @var Host $host */
            $args->setNewValue('hostsSorting', $host->getName());
        }
    }
}
