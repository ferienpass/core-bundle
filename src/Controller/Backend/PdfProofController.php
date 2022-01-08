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

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExports;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/angebot/{id}/vorschau", name="backend_offer_pdf_proof", requirements={"itemId"="\d+"})
 */
class PdfProofController extends AbstractController
{
    private PdfExports $pdfExports;

    public function __construct(PdfExports $pdfExports)
    {
        $this->pdfExports = $pdfExports;
    }

    public function __invoke(int $id, Request $request, OfferRepository $offerRepository)
    {
        $offer = $offerRepository->find($id);

        $hasPdf = $this->pdfExports->has();

        return $this->render('@FerienpassCore/Backend/offer-proof.html.twig', [
            'offer' => $offer,
            'hasPdf' => $hasPdf,
        ]);
    }
}
