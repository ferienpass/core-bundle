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
use Ferienpass\CoreBundle\Entity\Edition;
use Zenstruck\Foundry\Instantiator;
use Zenstruck\Foundry\ModelFactory;

class EditionFactory extends ModelFactory
{
    public function withTasks(array $tasks): self
    {
        return $this->addState(['tasks' => new ArrayCollection($tasks)]);
    }

    protected function getDefaults(): array
    {
        return [
            'name' => 'Ferienpass 2021',
            'alias' => 'ferienpass-2021',
            'tstamp' => time(),
        ];
    }

    protected function initialize(): self
    {
        // see https://github.com/zenstruck/foundry#initialization
        return $this
            ->instantiateWith((new Instantiator())->alwaysForceProperties(['tasks']))
            // ->afterInstantiate(function(Post $post) {})
            ;
    }

    protected static function getClass(): string
    {
        return Edition::class;
    }
}
