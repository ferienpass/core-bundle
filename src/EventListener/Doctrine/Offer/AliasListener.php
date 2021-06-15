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

use Contao\CoreBundle\Slug\Slug;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Ferienpass\CoreBundle\Entity\Offer;

class AliasListener
{
    private Slug $slug;

    public function __construct(Slug $slug)
    {
        $this->slug = $slug;
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Offer) {
            return;
        }

        $alias = ($entity->getId() ?? uniqid()).'-'.$this->slug->generate($entity->getName());
        $entity->setAlias($alias);
    }
}
