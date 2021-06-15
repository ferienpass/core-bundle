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

namespace Ferienpass\CoreBundle\Fixtures\Factory;

use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Participant;
use Zenstruck\Foundry\ModelFactory;

class AttendanceFactory extends ModelFactory
{
    public function withOffer($offer): self
    {
        return $this->addState(['offer' => $offer]);
    }

    public function withStatus(string $status): self
    {
        return $this->addState(['status' => $status]);
    }

    public function withParticipant(Participant $participant): self
    {
        return $this->addState(['participant' => $participant]);
    }

    protected function getDefaults(): array
    {
        return [
            'offer' => OfferFactory::new(),
            'participant' => ParticipantFactory::new(),
        ];
    }

    protected function initialize(): self
    {
        // see https://github.com/zenstruck/foundry#initialization
        return $this
            // ->afterInstantiate(function(Post $post) {})
            ;
    }

    protected static function getClass(): string
    {
        return Attendance::class;
    }
}
