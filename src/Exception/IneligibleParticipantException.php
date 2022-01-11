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

namespace Ferienpass\CoreBundle\Exception;

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;
use Symfony\Component\Translation\TranslatableMessage;

class IneligibleParticipantException extends \Exception
{
    public function __construct(private Offer $offer, private Participant $participant, private TranslatableMessage $userMessage)
    {
        parent::__construct((string) $userMessage);
    }

    public function getOffer(): Offer
    {
        return $this->offer;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }

    public function getUserMessage(): TranslatableMessage
    {
        return $this->userMessage;
    }
}
