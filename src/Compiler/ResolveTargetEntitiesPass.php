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

namespace Ferienpass\CoreBundle\Compiler;

use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Resolves given target entities (interfaces) with container parameters.
 */
class ResolveTargetEntitiesPass implements CompilerPassInterface
{
    private array $interfaces;

    public function __construct(array $interfaces)
    {
        $this->interfaces = $interfaces;
    }

    public function process(ContainerBuilder $container)
    {
        $this->resolve($container);
    }

    private function resolve(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('doctrine.orm.listeners.resolve_target_entity')) {
            throw new \RuntimeException('Cannot find Doctrine Target Entity Resolver Listener.');
        }

        $resolveTargetEntityListener = $container->findDefinition('doctrine.orm.listeners.resolve_target_entity');

        foreach ($this->interfaces as $interface => $model) {
            $interfaceImplementation = $this->getClass($container, $model);
            $resolveTargetEntityListener->addMethodCall('addResolveTargetEntity', [$interface, $interfaceImplementation, []]);
        }

        $resolveTargetEntityListener
            ->addTag('doctrine.event_listener', ['event' => Events::loadClassMetadata])
            ->addTag('doctrine.event_listener', ['event' => Events::onClassMetadataNotFound])
        ;
    }

    /**
     * @param string $key
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    private function getClass(ContainerBuilder $container, $key)
    {
        if ($container->hasParameter($key)) {
            return $container->getParameter($key);
        }

        if (class_exists($key)) {
            return $key;
        }

        throw new \InvalidArgumentException(sprintf('The class %s does not exist.', $key));
    }
}
