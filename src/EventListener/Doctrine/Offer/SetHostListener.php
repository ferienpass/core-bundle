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
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Repository\HostRepository;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, entity: OfferInterface::class)]
class SetHostListener
{
    public function __construct(private readonly HostRepository $hosts, private readonly Security $security)
    {
    }

    public function prePersist(OfferInterface $offer, PrePersistEventArgs $args): void
    {
        if (!$offer->getHosts()->isEmpty()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $hosts = $this->hosts->findByUser($user);
        if (empty($hosts)) {
            return;
        }

        $offer->addHost($hosts[0]);
    }
}
