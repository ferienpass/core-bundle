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
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/pdf-vorschau', name: 'backend_pdf_proof')]
final class ProofController extends AbstractBackendController
{
    public function __invoke(EditionRepository $editionRepository, OfferRepository $offerRepository, Request $request): Response
    {
        $edition = $editionRepository->find(13);
        $offers = $offerRepository->findBy(['edition' => $edition], null, 100);

        return $this->render('@FerienpassCore/Backend/be_pdf_proof.html.twig', [
            'offers' => $offers,
        ]);
    }
}
