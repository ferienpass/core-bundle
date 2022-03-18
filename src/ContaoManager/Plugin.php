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

namespace Ferienpass\CoreBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerBundle\ContaoManagerBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use Contao\ManagerPlugin\Dependency\DependentPluginInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Ferienpass\CoreBundle\FerienpassCoreBundle;
use Ferienpass\CoreBundle\Security\Authentication\AuthenticationFailureHandler;
use FOS\HttpCacheBundle\FOSHttpCacheBundle;
use Knp\Bundle\SnappyBundle\KnpSnappyBundle;
use Mvo\ContaoGroupWidget\MvoContaoGroupWidgetBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

class Plugin implements BundlePluginInterface, RoutingPluginInterface, ConfigPluginInterface, DependentPluginInterface, ExtensionPluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(SensioFrameworkExtraBundle::class),
            BundleConfig::create(FrameworkBundle::class),
            BundleConfig::create(KnpSnappyBundle::class),
            BundleConfig::create(FerienpassCoreBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    ContaoManagerBundle::class,
                    MvoContaoGroupWidgetBundle::class,
                    SensioFrameworkExtraBundle::class,
                    FOSHttpCacheBundle::class,
                    FrameworkBundle::class,
                    KnpSnappyBundle::class,
                    'notification_center',
                ]),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): ?RouteCollection
    {
        if ('test' === $kernel->getEnvironment()) {
            return $resolver
                ->resolve(__DIR__.'/../../config/routes_test.yml')
                ->load(__DIR__.'/../../config/routes_test.yml');
        }

        return $resolver
            ->resolve(__DIR__.'/../../config/routes.yml')
            ->load(__DIR__.'/../../config/routes.yml');
    }

    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig): void
    {
        if (isset($_ENV['SENTRY_DSN'])) {
            $loader->load(__DIR__.'/../../config/sentry.yml');
            $loader->load(__DIR__.'/../../config/packages/prod/monolog.yml');
        }

        $loader->load(__DIR__.'/../../config/contao.yml');
        $loader->load(__DIR__.'/../../config/packages/doctrine.yml');
        $loader->load(__DIR__.'/../../config/packages/monolog.yml');
        $loader->load(__DIR__.'/../../config/packages/messenger.yml');
        $loader->load(__DIR__.'/../../config/packages/framework.yml');
        // $loader->load(__DIR__.'/../../config/packages/privacydump.yml');
        $loader->load(__DIR__.'/../../config/twig.yml');
        // $loader->load(__DIR__.'/../../config/uploader.yml');
    }

    public function getPackageDependencies(): array
    {
        return [
            'friendsofsymfony/http-cache-bundle',
            'sentry/sentry-symfony',
            'lexik/maintenance-bundle',
            'symfony/monolog-bundle',
        ];
    }

    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        if ('security' === $extensionName) {
            foreach ($extensionConfigs as &$extensionConfig) {
                if (isset($extensionConfig['firewalls']['contao_frontend'])) {
                    $extensionConfig['firewalls']['contao_frontend']['json_login']['check_path'] = 'api_login';
                    $extensionConfig['firewalls']['contao_frontend']['json_login']['failure_handler'] = AuthenticationFailureHandler::class;
                    $extensionConfig['firewalls']['contao_frontend']['json_login']['remember_me'] = true;
                    break;
                }
            }
        }

        if ('sensio_framework_extra' === $extensionName) {
            foreach ($extensionConfigs as &$extensionConfig) {
                $extensionConfig['router']['annotations'] = false;
            }
        }

        return $extensionConfigs;
    }
}
