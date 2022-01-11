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

namespace Ferienpass\CoreBundle\Export\Offer\Xml;

use Contao\File;
use Contao\FilesModel;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\Offer\OffersExportInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class XmlExport implements OffersExportInterface
{
    private string $template;

    public function __construct(private Environment $twig)
    {
    }

    /**
     * @required
     */
    public function withTemplate(string $template): self
    {
        $clone = clone $this;
        $clone->template = $template;

        return $clone;
    }

    public function generate(iterable $offers, string $destination = null): string
    {
        $xml = $this->render($offers);
        $hash = md5($xml);
        $tmpPath = sys_get_temp_dir().'/xml';
        $xmlPath = sprintf('%s/%s.xml', $tmpPath, $hash);

        $filesystem = new Filesystem();
        $filesystem->mkdir($tmpPath);

        $filesystem->dumpFile($destination ?? $xmlPath, $xml);

        return $destination ?? $xmlPath;
    }

    private function render(iterable $items): string
    {
        $images = [];
        /** @var Offer $item */
        foreach ($items as $item) {
            foreach (array_filter($item->getHosts()->map(fn (Host $h) => FilesModel::findByPk($h->getLogo()))->toArray()) as $logo) {
                $images[$logo->uuid] = array_merge($logo->row(), [
                    'path' => $logo->path,
                    'dimensions' => (new File($logo->path))->imageViewSize,
                ]);
            }
        }

        return $this->twig->render($this->template, ['offers' => $items, 'images' => $images]);
    }
}
