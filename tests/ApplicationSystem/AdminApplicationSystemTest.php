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

use Ferienpass\CoreBundle\ApplicationSystem\AdminApplicationSystem;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;
use PHPUnit\Framework\TestCase;

class AdminApplicationSystemTest extends TestCase
{
    public function testStatusIsWaiting(): void
    {
        $applicationSystem = new AdminApplicationSystem();

        $attendance = new Attendance($offer = new Offer(), $participant = new Participant());

        $applicationSystem->assignStatus($attendance);

        self::assertSame('waiting', $attendance->getStatus());
        self::assertSame(128, $attendance->getSorting());
    }
}
