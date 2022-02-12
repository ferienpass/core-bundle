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
use Ferienpass\CoreBundle\DependencyInjection\FerienpassCoreExtension;
use Ferienpass\CoreBundle\Fragment\DashboardWidgetReference;
use Ferienpass\CoreBundle\Fragment\EditionStatsWidgetReference;
use Ferienpass\CoreBundle\Fragment\FragmentReference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FerienpassCoreBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension()
    {
        return new FerienpassCoreExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterFragmentsPass(FragmentReference::TAG_NAME));
        $container->addCompilerPass(new RegisterFragmentsPass(DashboardWidgetReference::TAG_NAME));
        $container->addCompilerPass(new RegisterFragmentsPass(EditionStatsWidgetReference::TAG_NAME));
    }
}
