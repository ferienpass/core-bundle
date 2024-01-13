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

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\Offer\Web\ImgExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['token_check' => true])]
final class WebProofController extends AbstractController
{
    public function __construct(private readonly TokenChecker $tokenChecker, private readonly ImgExport $imgExport)
    {
    }

    #[Route(path: '/angebot/web/{id}.{_format}', name: 'web-proof', defaults: ['format' => 'jpg'], requirements: ['itemId' => '\d+'])]
    public function __invoke(Offer $offer, string $_format, Request $request)
    {
        if (!\in_array($_format, ['jpg', 'jpeg'], true)) {
            throw new PageNotFoundException('Format not supported: '.$_format);
        }

        if (null !== $this->tokenChecker->getBackendUsername()) {
            $this->denyAccessUnlessGranted('view', $offer);
        }

        $imgPath = $this->imgExport->generate($offer);

        $contentDisposition = $request->query->get('dl')
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $downloadName = 'Korrekturabzug Web '.$offer->getId();

        $response = new BinaryFileResponse($imgPath);
        $response->headers->add([
            'Content-Type' => 'image/'.$_format,
            'Content-Disposition' => $response->headers->makeDisposition(
                $contentDisposition,
                $downloadName.'.'.$_format
            ),
        ]);

        return $response;
    }
}
