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

namespace Ferienpass\CoreBundle\Tests\ApplicationSystem;

use Ferienpass\CoreBundle\ApplicationSystem\LotApplicationSystem;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer\BaseOffer;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Fixtures\Factory\EditionTaskFactory;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\Test\Factories;

class LotApplicationSystemTest extends TestCase
{
    use Factories;

    public function testStatusIsWaiting(): void
    {
        $editionTask = EditionTaskFactory::new()->ofTypeLotApplicationSystem()->create();

        $applicationSystem = (new LotApplicationSystem())->withTask($editionTask->object());

        $attendance = new Attendance($offer = new BaseOffer(), $participant = new Participant());

        $applicationSystem->assignStatus($attendance, null);

        self::assertSame('waiting', $attendance->getStatus());
        self::assertSame(128, $attendance->getSorting());
    }
}
