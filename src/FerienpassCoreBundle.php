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
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class FerienpassCoreBundle extends AbstractBundle
{
    use PersistenceBundleTrait;

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return new FerienpassCoreExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $this->buildPersistence([
            OfferInterface::class => 'ferienpass.model.offer.class',
        ], $container);
    }
}
