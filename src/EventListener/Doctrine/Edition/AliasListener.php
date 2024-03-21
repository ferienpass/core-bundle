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

namespace Ferienpass\CoreBundle\EventListener\Doctrine\Edition;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Ferienpass\CoreBundle\Entity\Edition;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Edition::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Edition::class)]
class AliasListener
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

    public function prePersist(Edition $edition)
    {
        $edition->generateAlias($this->slugger);
    }

    public function preUpdate(Edition $edition)
    {
        $edition->generateAlias($this->slugger);
    }
}
