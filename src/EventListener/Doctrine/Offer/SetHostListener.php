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

use Contao\FrontendUser;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Offer;
use Symfony\Component\Security\Core\Security;

class SetHostListener
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Offer) {
            return;
        }

        if (!$entity->getHosts()->isEmpty()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof FrontendUser) {
            return;
        }

        $hosts = $args->getObjectManager()->getRepository(Host::class)->findByMemberId((int) $user->id);
        if (empty($hosts)) {
            return;
        }

        $entity->addHost($hosts[0]);
    }
}
