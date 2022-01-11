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

namespace Ferienpass\CoreBundle\ApplicationSystem;

use Ferienpass\CoreBundle\Entity\EditionTask;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Exception\AmbiguousApplicationSystemException;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ApplicationSystems implements ServiceSubscriberInterface
{
    public function __construct(private ContainerInterface $locator)
    {
    }

    public function findApplicationSystem(Offer $offer): ?ApplicationSystemInterface
    {
        // When the Ferienpass is configured without editions, use the "first come-first served" application procedure
        if (null === $edition = $offer->getEdition()) {
            return $this->locator->get('ferienpass.application_system.firstcome');
        }

        $tasks = $edition->getTasks()->filter(
            fn (EditionTask $t) => 'application_system' === $t->getType() && $t->isActive()
        );

        if ($tasks->count() > 1) {
            throw new AmbiguousApplicationSystemException('More than one application system is applicable at the moment for pass edition ID '.$edition->getId());
        }

        if ($tasks->isEmpty()) {
            return null;
        }

        $task = $tasks->current();

        $applicationSystem = $this->locator->get('ferienpass.application_system.'.$task->getApplicationSystem());
        if (!$applicationSystem instanceof ApplicationSystemInterface) {
            throw new \RuntimeException(sprintf('Application system "%s" is unknown', $task->getApplicationSystem()));
        }

        if ($applicationSystem instanceof TimedApplicationSystemInterface) {
            $applicationSystem = $applicationSystem->withTask($task);
        }

        return $applicationSystem;
    }

    public static function getSubscribedServices(): array
    {
        return [
            'ferienpass.application_system.lot' => LotApplicationSystem::class,
            'ferienpass.application_system.firstcome' => FirstComeApplicationSystem::class,
        ];
    }
}
