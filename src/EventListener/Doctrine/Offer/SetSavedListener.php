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
use Symfony\Component\HttpFoundation\Session\Session;

class SetSavedListener
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof Offer) {
            return;
        }

        if (!$this->session->isStarted()) {
            return;
        }

        $savedOffers = $this->session->get('saved_offers');
        if (!$savedOffers) {
            return;
        }

        if (\in_array($entity->getId(), $savedOffers, true)) {
            $entity->setSaved(true);
        }
    }
}
