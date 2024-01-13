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

namespace Ferienpass\CoreBundle\Export\Payments;

use Mpdf\Mpdf;
use Mpdf\Output\Destination as MpdfDestination;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment as TwigEnvironment;

final class PaymentsPdfExport implements PaymentsExportInterface
{
    public function __construct(private readonly Filesystem $filesystem, private readonly TwigEnvironment $twig)
    {
    }

    public function generate(iterable $payments, string $destination = null): string
    {
        $html = $this->renderHtml($payments);
        $hash = md5($html);
        $tmpPath = sys_get_temp_dir().'/system/tmp/pdf';
        $pdfPath = sprintf('%s/%s.pdf', $tmpPath, $hash);

        $this->filesystem->mkdir($tmpPath);
        $this->createPdf($pdfPath, $html);

        if (null !== $destination) {
            $this->filesystem->copy($pdfPath, $destination);
        }

        return $destination ?? $pdfPath;
    }

    private function renderHtml(iterable $items): string
    {
        return $this->twig->render('@FerienpassCore/Payments/Pdf/Page.html.twig', [
            'items' => $items,
        ]);
    }

    private function createPdf(string $path, string $html, array $mPdfConfig = []): void
    {
        if (file_exists($path)) {
            return;
        }

        $mPdfConfig = array_merge([
            'default_font_size' => 12,
            'default_font' => 'roboto',
            'fontDir' => \dirname(__DIR__).'/../../fonts/Roboto',
            'fontdata' => [
                'roboto' => [
                    'R' => 'Roboto-Regular.ttf',
                    'B' => 'Roboto-Bold.ttf',
                    'I' => 'Roboto-Italic.ttf',
                ],
                'roboto-light' => [
                    'R' => 'Roboto-Light.ttf',
                    'I' => 'Roboto-LightItalic.ttf',
                ],
                'roboto-thin' => [
                    'R' => 'Roboto-Thin.ttf',
                    'I' => 'Roboto-ThinItalic.ttf',
                ],
            ],
        ], $mPdfConfig);

        $mPdf = new Mpdf($mPdfConfig);

        $mPdf->WriteHTML($html);

        $mPdf->Output($path, MpdfDestination::FILE);
    }
}
