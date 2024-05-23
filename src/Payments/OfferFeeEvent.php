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

namespace Ferienpass\CoreBundle\Payments;

use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Entity\Participant\ParticipantInterface;

class OfferFeeEvent
{
    public function __construct(private ?int $fee, private readonly OfferInterface $offer, private readonly ParticipantInterface $participant)
    {
    }

    public function getFee(): int
    {
        return (int) $this->fee;
    }

    public function getOffer(): OfferInterface
    {
        return $this->offer;
    }

    public function getParticipant(): ParticipantInterface
    {
        return $this->participant;
    }

    public function setFee(int $fee): void
    {
        $this->fee = $fee;
    }
}
