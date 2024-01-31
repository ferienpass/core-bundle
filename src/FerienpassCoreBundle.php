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

use Ferienpass\CoreBundle\DependencyInjection\FerienpassCoreExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
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
}
