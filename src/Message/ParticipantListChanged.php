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

namespace Ferienpass\CoreBundle\Message;

use Ferienpass\CoreBundle\Entity\Offer;

/**
 * This message is dispatched when an attendance of a particular offer is added, removed, or changed.
 */
class ParticipantListChanged implements LoggableMessageInterface
{
    public function __construct(private readonly int $offerId)
    {
    }

    public function getOfferId(): int
    {
        return $this->offerId;
    }

    public function getRelated(): array
    {
        return [
            Offer::class => $this->offerId,
        ];
    }
}
