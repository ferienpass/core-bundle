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

final class PdfExports
{
    /**
     * @var PdfExportConfig[]
     */
    private array $configs = [];
    private PdfExport $pdfExport;

    public function __construct(PdfExport $pdfExport)
    {
        $this->pdfExport = $pdfExport;
    }

    public function addConfig(string $key, array $config): void
    {
        $this->configs[$key] = new PdfExportConfig($config['mpdf_config'] ?? [], $config['template'] ?? null);
    }

    public function getConfig(string $key = 'print'): PdfExportConfig
    {
        if (!isset($this->configs[$key])) {
            throw new \LogicException('PDF Export not supported');
        }

        return $this->configs[$key];
    }

    public function getNames(): array
    {
        return array_keys($this->configs);
    }

    public function has(string $key = 'print'): bool
    {
        return isset($this->configs[$key]);
    }

    public function get(string $key = 'print'): PdfExport
    {
        if (!isset($this->configs[$key])) {
            throw new \LogicException('PDF Export not supported');
        }

        return $this->pdfExport->withConfig($this->configs[$key]);
    }
}
