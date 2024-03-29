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

use Ferienpass\CoreBundle\Entity\Participant\ParticipantInterface;
use Ferienpass\CoreBundle\Repository\ParticipantRepositoryInterface;
use Symfony\UX\LiveComponent\Hydration\HydrationExtensionInterface;

class ParticipantHydrationExtension implements HydrationExtensionInterface
{
    public function __construct(private readonly ParticipantRepositoryInterface $repository)
    {
    }

    public function supports(string $className): bool
    {
        return is_a($className, ParticipantInterface::class, true);
    }

    public function hydrate(mixed $value, string $className): ?object
    {
        // an empty array means a non-persisted entity
        // we support instantiating with no constructor args
        if (\is_array($value) && 0 === \count($value)) {
            return $this->repository->createNew();
        }

        // e.g. an empty string
        if (!$value) {
            return null;
        }

        // $data is the single identifier or array of identifiers
        if (\is_scalar($value) || (\is_array($value) && isset($value[0]))) {
            return $this->repository->find($value);
        }

        throw new \InvalidArgumentException(sprintf('Cannot hydrate Doctrine entity "%s". Value of type "%s" is not supported.', $className, get_debug_type($value)));
    }

    public function dehydrate(object $object): mixed
    {
        return $object->getId() ?? [];
    }
}
