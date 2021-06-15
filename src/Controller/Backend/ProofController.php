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

use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Entity\Offer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pdf-vorschau", name="backend_pdf_proof")
 */
final class ProofController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $edition = $this->getDoctrine()->getRepository(Edition::class)->find(13);
        $offers = $this->getDoctrine()->getRepository(Offer::class)->findBy(['edition' => $edition], null, 100);

        return $this->render('@FerienpassCore/Backend/be_pdf_proof.html.twig', [
            'offers' => $offers,
        ]);
    }
}
