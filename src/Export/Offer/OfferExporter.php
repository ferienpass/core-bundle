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

namespace Ferienpass\CoreBundle\Export\Offer;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class OfferExporter
{
    /**
     * @var array<string, OfferExportTypeInterface>
     */
    private array $exportTypes;

    public function __construct(#[TaggedIterator('ferienpass.offer_export_type', indexAttribute: 'key')] iterable $types)
    {
        $this->exportTypes = $types instanceof \Traversable ? iterator_to_array($types, true) : $types;
    }

    public function getAllNames(): array
    {
        $names = [];
        foreach ($this->exportTypes as $key => $type) {
            foreach ($type->getNames() as $name) {
                $names[] = $key.'.'.$name;
            }
        }

        return $names;
    }

    public function getExporter(string $name): OffersExportInterface
    {
        [$type, $key] = explode('.', $name, 2);

        if (null !== $exporter = $this->exportTypes[$type]?->get($key)) {
            return $exporter;
        }

        throw new \InvalidArgumentException(sprintf('Type "%s" is not supported', $name));
    }
}
