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

use Doctrine\Common\Collections\ArrayCollection;
use Ferienpass\CoreBundle\Entity\Offer;
use function Zenstruck\Foundry\faker;
use Zenstruck\Foundry\Instantiator;
use Zenstruck\Foundry\ModelFactory;

class OfferFactory extends ModelFactory
{
    public function withMaxParticipants(int $max): self
    {
        return $this->addState(['maxParticipants' => $max]);
    }

    public function withEdition($edition): self
    {
        return $this->addState(['edition' => $edition]);
    }

    public function withAttendances(array $attendances): self
    {
        return $this->addState(['attendances' => new ArrayCollection($attendances)]);
    }

    public function withDates(): self
    {
        return $this->addState(['dates' => OfferDateFactory::createMany(1, ['offer' => $this])]);
    }

    protected function getDefaults(): array
    {
        return [
            'name' => faker()->realText(40),
            'alias' => 'ferienpass-2021',
        ];
    }

    protected function initialize(): self
    {
        // see https://github.com/zenstruck/foundry#initialization
        return $this
            ->instantiateWith((new Instantiator())->alwaysForceProperties(['attendances']))
            // ->afterInstantiate(function(Post $post) {})
            ;
    }

    protected static function getClass(): string
    {
        return Offer::class;
    }
}
