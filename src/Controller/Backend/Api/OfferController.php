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

namespace Ferienpass\CoreBundle\Controller\Backend\Api;

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Message\OfferCancelled;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/offer/{id}", requirements={"id"="\d+"})
 */
final class OfferController extends AbstractController
{
    /**
     * @Route("/cancel", name="backend_api_offer_cancel", methods={"POST"})
     */
    public function cancelOffer(Offer $offer): Response
    {
        $this->get('contao.framework')->initialize();

        $this->checkToken();

        if ($offer->isCancelled()) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $offer->setCancelled(true);

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $this->dispatchMessage(new OfferCancelled($offer->getId()));

        return new Response('', Response::HTTP_OK);
    }
}
