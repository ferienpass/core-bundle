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

use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;

interface OfferExportInterface
{
    public function generate(OfferInterface $offer, string $destination = null): string;
}
