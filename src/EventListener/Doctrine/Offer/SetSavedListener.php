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
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEntityListener(event: Events::postLoad, entity: OfferInterface::class)]
class SetSavedListener
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function postLoad(OfferInterface $offer, PostLoadEventArgs $args): void
    {
        try {
            if (!$this->requestStack->getSession()->isStarted()) {
                return;
            }
        } catch (SessionNotFoundException) {
            return;
        }

        $savedOffers = $this->requestStack->getSession()->get('saved_offers');
        if (!$savedOffers) {
            return;
        }

        if (\in_array($offer->getId(), $savedOffers, true)) {
            $offer->setSaved(true);
        }
    }
}
