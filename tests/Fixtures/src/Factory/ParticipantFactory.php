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

use Ferienpass\CoreBundle\Entity\Participant\BaseParticipant;
use Zenstruck\Foundry\ModelFactory;
use function Zenstruck\Foundry\faker;

class ParticipantFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'firstname' => faker()->firstName(),
            'lastname' => faker()->name(),
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
        return BaseParticipant::class;
    }
}
