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
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsDoctrineListener('postLoad')]
class SetSavedListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!$entity instanceof OfferInterface) {
            return;
        }

        try {
            if (!$this->requestStack->getSession()->isStarted()) {
                return;
            }
        } catch (SessionNotFoundException $exception) {
            return;
        }

        $savedOffers = $this->requestStack->getSession()->get('saved_offers');
        if (!$savedOffers) {
            return;
        }

        if (\in_array($entity->getId(), $savedOffers, true)) {
            $entity->setSaved(true);
        }
    }
}
