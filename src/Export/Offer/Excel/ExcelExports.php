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

namespace Ferienpass\CoreBundle\Export\Offer\Excel;

use Ferienpass\CoreBundle\Export\Offer\OfferExportTypeInterface;
use Ferienpass\CoreBundle\Export\Offer\OffersExportInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class ExcelExports implements OfferExportTypeInterface
{
    /**
     * @var array<string, ExcelExportInterface>
     */
    private array $exports;

    public function __construct(#[TaggedIterator('ferienpass.export.offer.xlsx', indexAttribute: 'key')] iterable $exports)
    {
        $this->exports = $exports instanceof \Traversable ? iterator_to_array($exports, true) : $exports;
    }

    public function getNames(): array
    {
        return array_keys($this->exports);
    }

    public function has(string $key): bool
    {
        return isset($this->exports[$key]);
    }

    public function get(string $key): OffersExportInterface
    {
        if (!isset($this->exports[$key])) {
            throw new \LogicException('Excel export not supported');
        }

        return $this->exports[$key];
    }
}
