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

namespace Ferienpass\CoreBundle\Export\ParticipantList;

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\Offer\OfferExportInterface;
use Mpdf\Mpdf;
use Mpdf\Output\Destination as MpdfDestination;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment as TwigEnvironment;

final class PdfExport implements OfferExportInterface
{
    private Filesystem $filesystem;
    private TwigEnvironment $twig;
    private string $rootDir;

    public function __construct(Filesystem $filesystem, TwigEnvironment $twig, string $rootDir)
    {
        $this->filesystem = $filesystem;
        $this->twig = $twig;
        $this->rootDir = $rootDir;
    }

    public function generate(Offer $offer, string $destination = null): string
    {
        $html = $this->renderHtml($offer);
        $hash = md5($html);
        $tmpPath = $this->rootDir.'/system/tmp/pdf';
        $pdfPath = sprintf('%s/%s.pdf', $tmpPath, $hash);

        $this->filesystem->mkdir($tmpPath);
        $this->createPdf($pdfPath, $html);

        if (null !== $destination) {
            $this->filesystem->copy($pdfPath, $destination);
        }

        return $destination ?? $pdfPath;
    }

    private function renderHtml(Offer $offer): string
    {
        $attendances = $offer->getAttendancesNotWithdrawn();

        return $this->twig->render('@FerienpassCore/ParticipantList/Pdf/Page.html.twig', [
            'attendances' => $attendances,
            'offer' => $offer,
        ]);
    }

    private function createPdf(string $path, string $html, array $mPdfConfig = []): void
    {
        if (file_exists($path)) {
            return;
        }

        $mPdfConfig = array_merge([
            'default_font_size' => 12,
            'default_font' => 'helvetica',
        ], $mPdfConfig);

        $mPdf = new Mpdf($mPdfConfig);

        $mPdf->WriteHTML($html);

        $mPdf->Output($path, MpdfDestination::FILE);
    }
}
