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
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExports;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/angebot/druck/{id}.{_format}", name="pdf-proof", defaults={"format"="pdf"}, requirements={"id"="\d+"})
 */
final class PrintSheetProofController extends AbstractController
{
    private TokenChecker $tokenChecker;
    private PdfExports $pdfExports;

    public function __construct(TokenChecker $tokenChecker, PdfExports $pdfExports)
    {
        $this->tokenChecker = $tokenChecker;
        $this->pdfExports = $pdfExports;
    }

    public function __invoke(int $id, string $_format, Request $request, OfferRepository $offerRepository)
    {
        if (!\in_array($_format, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
            throw new PageNotFoundException('Format not supported: '.$_format);
        }

        if (!$this->pdfExports->has()) {
            throw new PageNotFoundException('No print template');
        }

        $offer = $offerRepository->find($id);
        if (null === $offer) {
            return new Response('Item not found', Response::HTTP_NOT_FOUND);
        }

        if (null === $this->tokenChecker->getBackendUsername()) {
            $this->denyAccessUnlessGranted('view', $offer);
        }

        $pdfPath = $this->pdfExports->get()->generate([$offer]);

        $contentDisposition = $request->query->get('dl')
            ? ResponseHeaderBag::DISPOSITION_ATTACHMENT
            : ResponseHeaderBag::DISPOSITION_INLINE;

        $downloadName = 'Korrekturabzug '.$offer->getId();

        if ('pdf' === $_format) {
            $response = new BinaryFileResponse($pdfPath);
            $response->headers->add([
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $response->headers->makeDisposition($contentDisposition, $downloadName.'.pdf'),
            ]);

            return $response;
        }

        $imgPath = str_replace('.pdf', '.'.$_format, $pdfPath);

        $this->createImage($_format, $imgPath, $pdfPath);

        $response = new BinaryFileResponse($imgPath);
        $response->headers->add([
            'Content-Type' => 'image/'.$_format,
            'Content-Disposition' => $response->headers->makeDisposition($contentDisposition, $downloadName.'.'.$_format),
        ]);

        return $response;
    }

    private function createImage(string $format, string $imgPath, string $pdfPath): void
    {
        if (file_exists($imgPath)) {
            return;
        }

        if (!class_exists('Spatie\PdfToImage\Pdf')) {
            throw new \LogicException('PdfToImage extension is not available');
        }

        /** @psalm-suppress UndefinedClass */
        $pdfToImage = new \Spatie\PdfToImage\Pdf($pdfPath);
        $pdfToImage->setOutputFormat($format)->saveImage($imgPath);
    }
}
