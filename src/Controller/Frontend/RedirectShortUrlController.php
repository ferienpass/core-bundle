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

namespace Ferienpass\CoreBundle\Controller\Frontend;

use Ferienpass\CoreBundle\Entity\Offer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/{id}', requirements: ['id' => '\d+'])]
class RedirectShortUrlController extends \Contao\CoreBundle\Controller\AbstractController
{
    public function __invoke(Offer $offer)
    {
        if ($offer->isVariant() || $offer->hasVariants()) {
            $base = $offer->getVariantBase() ? $offer->getVariantBase()->getId() : $offer->getId();

            return $this->redirectToRoute('offer_list', ['base' => $base], Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->redirectToRoute('offer_details', ['alias' => $offer->getAlias()], Response::HTTP_MOVED_PERMANENTLY);
    }
}
