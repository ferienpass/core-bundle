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

final class PdfExportConfig
{
    private array $mpdfConfig;
    private ?string $template;

    public function __construct(array $mpdfConfig, ?string $template)
    {
        $this->mpdfConfig = $mpdfConfig;
        $this->template = $template;
    }

    public function getMpdfConfig(): array
    {
        return $this->mpdfConfig;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }
}
