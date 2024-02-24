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

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Message\OfferCancelled;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Attribute\AsEnteredListener;
use Symfony\Component\Workflow\Event\EnteredEvent;

#[AsEnteredListener(workflow: 'offer', place: Offer::STATE_CANCELLED)]
class OfferCancelTransactionListener
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public function __invoke(EnteredEvent $event)
    {
        if (!($offer = $event->getSubject()) instanceof Offer) {
            throw new \RuntimeException('Unexpected event subject');
        }

        $this->messageBus->dispatch(new OfferCancelled($offer->getId()));
    }
}
