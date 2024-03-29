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

use Ferienpass\CoreBundle\Entity\Offer\BaseOffer;

interface OffersExportInterface
{
    /**
     * @param iterable<int, BaseOffer>
     */
    public function generate(iterable $offers, string $destination = null): string;
}
