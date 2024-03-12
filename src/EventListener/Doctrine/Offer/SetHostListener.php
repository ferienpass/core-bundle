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
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

#[AsDoctrineListener('prePersist')]
class SetHostListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof OfferInterface) {
            return;
        }

        if (!$entity->getHosts()->isEmpty()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $hosts = $args->getObjectManager()->getRepository(Host::class)->findByUser($user);
        if (empty($hosts)) {
            return;
        }

        $entity->addHost($hosts[0]);
    }
}
