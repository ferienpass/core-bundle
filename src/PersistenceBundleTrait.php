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

namespace Ferienpass\CoreBundle;

use Ferienpass\CoreBundle\Compiler\ResolveTargetEntitiesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait PersistenceBundleTrait
{
    /**
     * Build persistence adds a `ResolveTargetEntitiesPass` for the given interfaces.
     *
     * @param mixed[] $interfaces Target entities resolver configuration.
     *                            Mapping interfaces to a concrete implementation
     *
     * @return void
     */
    public function buildPersistence(array $interfaces, ContainerBuilder $container)
    {
        if (!empty($interfaces)) {
            $container->addCompilerPass(
                new ResolveTargetEntitiesPass($interfaces), priority: 10
            );
        }
    }
}
