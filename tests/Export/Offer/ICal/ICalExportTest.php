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

namespace Ferienpass\CoreBundle\Tests\Export\Offer\ICal;

use Ferienpass\CoreBundle\Export\Offer\ICal\ICalExport;
use Ferienpass\CoreBundle\Fixtures\Factory\OfferFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Zenstruck\Foundry\Test\Factories;

class ICalExportTest extends TestCase
{
    use Factories;

    public function testCreatesICal(): void
    {
        $offers = OfferFactory::new()->withDates()->many(3)->create();

        $filesystem = new Filesystem();
        $iCalExport = new ICalExport($filesystem, sys_get_temp_dir());

        $path = $iCalExport->generate($offers);

        $this->assertStringEndsWith('.ics', $path);
    }
}
