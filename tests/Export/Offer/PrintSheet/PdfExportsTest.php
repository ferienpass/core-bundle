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
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExports;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\Test\Factories;

class PdfExportsTest extends TestCase
{
    use Factories;

    public function testFetchesExporter(): void
    {
        $pdfExport = $this->createMock(PdfExport::class);

        $pdfExports = new PdfExports($pdfExport);

        self::assertFalse($pdfExports->has());

        $pdfExports->addConfig('print', ['mpdf_config' => [], 'template' => 'MyPage.html.twig']);

        self::assertTrue($pdfExports->has());
        self::assertTrue($pdfExports->has('print'));
        self::assertCount(1, $pdfExports->getNames());

        self::assertNotNull($pdfExports->get());

        self::assertSame('MyPage.html.twig', $pdfExports->getConfig()->getTemplate());
    }
}
