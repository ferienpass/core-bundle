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

namespace Ferienpass\CoreBundle\EventListener\Workflow;

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\OfferLog;
use Ferienpass\CoreBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Workflow\Attribute\AsAnnounceListener;
use Symfony\Component\Workflow\Event\AnnounceEvent;

#[AsAnnounceListener(workflow: 'offer')]
class OfferLogTransactionListener
{
    public function __construct(private readonly Security $security, private readonly EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(AnnounceEvent $event)
    {
        $offer = $event->getSubject();
        if (!$offer instanceof Offer) {
            throw new \RuntimeException('Unexpected event subject');
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $log = new OfferLog($offer, $user, transition: $event->getTransition());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
