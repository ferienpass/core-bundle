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

namespace Ferienpass\CoreBundle\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Ferienpass\CoreBundle\Repository\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Define repository services for each object (e.g. ferienpass.repository.[object name]) and
 * map all object parameters to the container.
 */
trait PersistenceExtensionTrait
{
    /**
     * @param array<string, array{model: class-string, repository?: class-string}> $objects
     *
     * @return void
     */
    protected function configurePersistence(array $objects, ContainerBuilder $container)
    {
        $this->defineRepositories($objects, $container);

        $this->remapObjectParameters($objects, $container);

        $configObjects = ['ferienpass' => $objects];

        if ($container->hasParameter('ferienpass.persistence.objects')) {
            /** @var mixed[] $existingConfigObjects */
            $existingConfigObjects = $container->getParameter('ferienpass.persistence.objects');
            $configObjects = array_merge_recursive($configObjects, $existingConfigObjects);
        }

        $container->setParameter('ferienpass.persistence.objects', $configObjects);
    }

    /**
     * @param array<string, array{model: class-string, repository?: class-string}> $objects
     */
    private function defineRepositories(array $objects, ContainerBuilder $container): void
    {
        foreach ($objects as $object => $services) {
            if (\array_key_exists('model', $services)) {
                $repositoryDefinition = $this->getRepositoryDefinition($object, $services, $container);

                $container->setDefinition($this->getContainerKey('repository', $object), $repositoryDefinition)
                    ->setPublic(true)
                    ->setLazy(true)
                ;
            }
        }
    }

    /**
     * Get the repository service definition.
     *
     * @param string                                                $object
     * @param array{model: class-string, repository?: class-string} $services
     *
     * @return Definition
     */
    private function getRepositoryDefinition($object, array $services, ContainerBuilder $container)
    {
        $repositoryKey = $this->getContainerKey('repository', $object, '.class');

        // default repository
        $repositoryClass = EntityRepository::class;

        if ($container->hasParameter($repositoryKey)) {
            /** @var class-string $repositoryClass */
            $repositoryClass = $container->getParameter($repositoryKey);
        }

        if (isset($services['repository'])) {
            $repositoryClass = $services['repository'];
        }

        $definition = new Definition($repositoryClass);
        $definition->setArguments([
            new Reference($this->getEntityManagerServiceKey()),
            $this->getClassMetadataDefinition($services['model']),
        ]);

        $repositoryReflectionClass = new \ReflectionClass($repositoryClass);
        if (
            $repositoryReflectionClass->hasMethod('setAccessControlQueryEnhancer')
            && !$repositoryReflectionClass->implementsInterface(ServiceEntityRepositoryInterface::class)
        ) {
            $definition->addMethodCall(
                'setAccessControlQueryEnhancer',
                [new Reference('ferienpass_security.access_control_query_enhancer')]
            );
        }

        return $definition;
    }

    /**
     * @param class-string $model
     *
     * @return Definition
     */
    private function getClassMetadataDefinition($model)
    {
        $definition = new Definition(ClassMetadata::class);
        $definition
            ->setFactory([
                new Reference($this->getEntityManagerServiceKey()),
                'getClassMetadata',
            ])
            ->setArguments([$model])
            ->setPublic(false)
        ;

        return $definition;
    }

    /**
     * @param array<string, array{model: class-string, repository?: class-string}> $objects
     */
    private function remapObjectParameters(array $objects, ContainerBuilder $container): void
    {
        foreach ($objects as $object => $services) {
            foreach ($services as $service => $class) {
                $container->setParameter(
                    sprintf(
                        'ferienpass.%s.%s.class',
                        $service,
                        $object
                    ),
                    $class
                );
            }
        }
    }

    /**
     * Get container key.
     *
     * @param string      $key
     * @param string      $object
     * @param string|null $suffix
     *
     * @return string
     */
    private function getContainerKey($key, $object, $suffix = null)
    {
        return sprintf('ferienpass.%s.%s%s', $key, $object, $suffix);
    }

    /**
     * Get the entity manager.
     *
     * @return string
     */
    private function getEntityManagerServiceKey()
    {
        return 'doctrine.orm.default_entity_manager';
    }
}
