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

namespace Ferienpass\CoreBundle\DependencyInjection\Compiler;

use Ferienpass\CoreBundle\UserAccount\UserAccountFragments;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class UserAccountFragmentsPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(UserAccountFragments::class)) {
            return;
        }

        $definition = $container->findDefinition(UserAccountFragments::class);

        $taggedServices = $container->findTaggedServiceIds('ferienpass.user_account');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addFragment', [
                    $attributes['key'],
                    $attributes['alias'],
                    $attributes['icon'],
                ]);
            }
        }
    }
}
