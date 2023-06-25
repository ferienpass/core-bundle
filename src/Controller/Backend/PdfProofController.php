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

namespace Ferienpass\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Controller\AbstractBackendController;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExports;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/angebot/{id}/vorschau', name: 'backend_offer_pdf_proof', requirements: ['itemId' => '\d+'])]
class PdfProofController extends AbstractBackendController
{
    public function __construct(private PdfExports $pdfExports)
    {
    }

    public function __invoke(Offer $offer, Request $request, ManagerRegistry $doctrine): Response
    {
        if (!$offer->getAlias()) {
            // Later, the AliasListener kicks in
            $offer->setAlias(uniqid());
            $doctrine->getManager()->flush();

            return $this->redirectToRoute($request->attributes->get('_route'), ['id' => $offer->getId()]);
        }

        return $this->render('@FerienpassCore/Backend/offer-proof.html.twig', [
            'offer' => $offer,
            'hasPdf' => $this->pdfExports->has(),
        ]);
    }
}
