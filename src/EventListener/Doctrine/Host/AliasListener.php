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

namespace Ferienpass\CoreBundle\EventListener\Doctrine\Host;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Ferienpass\CoreBundle\Entity\Host;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Host::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Host::class)]
class AliasListener
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function prePersist(Host $host)
    {
        $host->generateAlias($this->slugger);
    }

    public function preUpdate(Host $host)
    {
        $host->generateAlias($this->slugger);
    }
}
