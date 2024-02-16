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

namespace Ferienpass\CoreBundle\Export\Offer\PrintSheet;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\FilesModel;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\Offer\OffersExportInterface;
use Mpdf\Mpdf;
use Mpdf\Output\Destination as MpdfDestination;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class PdfExport implements OffersExportInterface
{
    private PdfExportConfig $config;

    public function __construct(private Filesystem $filesystem, #[Autowire('%kernel.project_dir%')] private string $projectDir, private Environment $twig, private readonly ContaoFramework $contaoFramework)
    {
    }

    public function withConfig(PdfExportConfig $config): self
    {
        $clone = clone $this;
        $clone->config = $config;

        return $clone;
    }

    public function generate(iterable $offers, string $destination = null): string
    {
        ini_set('pcre.backtrack_limit', '100000000');

        $html = $this->render($offers);
        $hash = md5($html);
        $tmpPath = sys_get_temp_dir().'/pdf';
        $pdfPath = sprintf('%s/%s.pdf', $tmpPath, $hash);

        $this->filesystem->mkdir($tmpPath);
        $this->createPdf($pdfPath, $html, $this->config->getMpdfConfig());

        if (null !== $destination) {
            $this->filesystem->copy($pdfPath, $destination);
        }

        return $destination ?? $pdfPath;
    }

    private function render(iterable $items): string
    {
        $this->contaoFramework->initialize();

        $images = [];
        /** @var Offer $item */
        foreach ($items as $item) {
            foreach (array_filter($item->getHosts()->map(fn (Host $h) => FilesModel::findByPk($h->getLogo()))->toArray()) as $logo) {
                $images[$logo->uuid] = array_merge($logo->row(), [
                    'path' => $this->projectDir.'/'.$logo->path,
                    'dimensions' => (new File($logo->path))->imageViewSize,
                ]);
            }
        }

        return $this->twig->render($this->config->getTemplate(), [
            'items' => $items,
            'images' => $images,
        ]);
    }

    private function createPdf(string $path, string $html, array $mPdfConfig): void
    {
        $mPdfConfig = array_merge(['tempDir' => sys_get_temp_dir()], $mPdfConfig);
        if (file_exists($path)) {
            return;
        }

        $mPdf = new Mpdf($mPdfConfig);

        $mPdf->WriteHTML($html);

        $mPdf->Output($path, MpdfDestination::FILE);
    }
}
