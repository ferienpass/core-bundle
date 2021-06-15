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

namespace Ferienpass\CoreBundle\Tests\Export\ParticipantList;

use Ferienpass\CoreBundle\Export\ParticipantList\PdfExport;
use Ferienpass\CoreBundle\Fixtures\Factory\OfferFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Zenstruck\Foundry\Test\Factories;

class PdfExportTest extends TestCase
{
    use Factories;

    public function testCreatesPdf()
    {
        $offer = OfferFactory::createOne();

        $filesystem = new Filesystem();
        $twig = $this->createMock(Environment::class);
        $twig
            ->expects($this->once())
            ->method('render')
            ->willReturn("<h1>Participant List: {$offer->getName()}</h1>")
            ;

        $pdfExport = new PdfExport($filesystem, $twig, sys_get_temp_dir());

        $path = $pdfExport->generate($offer->object());

        self::assertStringEndsWith('.pdf', $path);
    }
}
