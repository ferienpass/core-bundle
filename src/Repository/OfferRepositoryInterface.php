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

namespace Ferienpass\CoreBundle\Repository;

use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;

interface OfferRepositoryInterface extends RepositoryInterface
{
    public function findByAlias(string $alias): ?OfferInterface;

    public function createCopy(OfferInterface $original): OfferInterface;

    public function createVariant(OfferInterface $original): OfferInterface;

    public function updateVariant(OfferInterface $variant): void;
}
