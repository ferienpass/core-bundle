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

use Contao\CoreBundle\DependencyInjection\Compiler\RegisterFragmentsPass;
use Ferienpass\CoreBundle\DependencyInjection\Compiler\UserAccountFragmentsPass;
use Ferienpass\CoreBundle\DependencyInjection\FerienpassCoreExtension;
use Ferienpass\CoreBundle\Fragment\FragmentReference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class FerienpassCoreBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new FerienpassCoreExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterFragmentsPass(FragmentReference::TAG_NAME));
        $container->addCompilerPass(new UserAccountFragmentsPass());
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        if (isset($_ENV['SENTRY_DSN'])) {
            // $loader->load(__DIR__.'/../config/sentry.yml');
            $container->import(__DIR__.'/../config/packages/prod/monolog.php');
        }

        $container->import(__DIR__.'/../config/packages/contao.php');
        $container->import(__DIR__.'/../config/packages/doctrine.php');
        $container->import(__DIR__.'/../config/packages/monolog.php');
        $container->import(__DIR__.'/../config/packages/messenger.php');
        $container->import(__DIR__.'/../config/packages/framework.php');
        // $container->import(__DIR__.'/../config/packages/privacydump.php');
        $container->import(__DIR__.'/../config/twig.php');
        // $container->import(__DIR__.'/../config/uploader.yml');
    }
}
