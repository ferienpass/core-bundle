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

namespace Ferienpass\CoreBundle\Tests\Export\Offer\PrintSheet;

use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExport;
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExportConfig;
use Ferienpass\CoreBundle\Fixtures\Factory\OfferFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Zenstruck\Foundry\Test\Factories;

class PdfExportTest extends TestCase
{
    use Factories;

    public function testCreatesPdf(): void
    {
        $offer = OfferFactory::createOne();

        $filesystem = new Filesystem();
        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->once())
            ->method('render')
            ->with('MyPage.html.twig')
            ->willReturn("<h1>{$offer->getName()}</h1>")
            ;

        $pdfConfig = new PdfExportConfig([], 'MyPage.html.twig');
        $pdfExport = (new PdfExport($filesystem, sys_get_temp_dir(), $twig))->withConfig($pdfConfig);

        $path = $pdfExport->generate([$offer]);

        self::assertStringEndsWith('.pdf', $path);
    }
}
