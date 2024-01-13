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

namespace Ferienpass\CoreBundle\Controller\Frontend\Api;

use Ferienpass\CoreBundle\Entity\Offer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/offer')]
final class OfferController extends AbstractController
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    #[Route(path: '/{id}/save', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function save(Offer $offer): Response
    {
        $savedOffers = $this->session->get('saved_offers', []);

        if ($offer->isSaved()) {
            $savedOffers = array_filter($savedOffers, fn ($v) => $v !== $offer->getId());
        } else {
            $savedOffers[] = $offer->getId();
        }

        $this->requestStack->getSession()->set('saved_offers', $savedOffers);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
