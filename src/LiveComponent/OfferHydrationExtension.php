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

namespace Ferienpass\CoreBundle\LiveComponent;

use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Repository\OfferRepositoryInterface;
use Symfony\UX\LiveComponent\Hydration\HydrationExtensionInterface;

class OfferHydrationExtension implements HydrationExtensionInterface
{
    public function __construct(private readonly OfferRepositoryInterface $repository)
    {
    }

    public function supports(string $className): bool
    {
        return is_a($className, OfferInterface::class, true);
    }

    public function hydrate(mixed $value, string $className): ?object
    {
        return $this->repository->find($value);
    }

    public function dehydrate(object $object): mixed
    {
        return $object->getId();
    }
}
