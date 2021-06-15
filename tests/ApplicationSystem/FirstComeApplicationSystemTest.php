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

use Ferienpass\CoreBundle\ApplicationSystem\FirstComeApplicationSystem;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Fixtures\Factory\AttendanceFactory;
use Ferienpass\CoreBundle\Fixtures\Factory\EditionTaskFactory;
use Ferienpass\CoreBundle\Fixtures\Factory\OfferFactory;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\Test\Factories;

class FirstComeApplicationSystemTest extends TestCase
{
    use Factories;

    public function testStatusIsConfirmedWhenNoLimit(): void
    {
        $editionTask = EditionTaskFactory::new()->ofTypeFirstComeApplicationSystem()->create();

        $applicationSystem = (new FirstComeApplicationSystem())->withTask($editionTask->object());

        $attendance = new Attendance(new Offer(), new Participant());

        $applicationSystem->assignStatus($attendance);

        self::assertSame('confirmed', $attendance->getStatus());
        self::assertSame(128, $attendance->getSorting());
    }

    public function testDoesNothingIfNotWaitlisted(): void
    {
        $editionTask = EditionTaskFactory::new()->ofTypeFirstComeApplicationSystem()->create();

        $applicationSystem = (new FirstComeApplicationSystem())->withTask($editionTask->object());

        $applicationSystem->assignStatus($attendance = new Attendance(new Offer(), new Participant(), Attendance::STATUS_WITHDRAWN));

        self::assertSame('withdrawn', $attendance->getStatus());

        $applicationSystem->assignStatus($attendance = new Attendance(new Offer(), new Participant(), Attendance::STATUS_WAITING));

        self::assertSame('waiting', $attendance->getStatus());
    }

    public function testAttendanceBecomesConfirmedWithoutLimit(): void
    {
        $editionTask = EditionTaskFactory::new()->ofTypeFirstComeApplicationSystem()->create();

        $applicationSystem = (new FirstComeApplicationSystem())->withTask($editionTask->object());

        $applicationSystem->assignStatus($attendance = new Attendance(new Offer(), new Participant(), Attendance::STATUS_WAITLISTED));

        self::assertSame('confirmed', $attendance->getStatus());
    }

    public function testAttendanceBecomesConfirmedWithLimit(): void
    {
        $editionTask = EditionTaskFactory::new()->ofTypeFirstComeApplicationSystem()->create();

        $applicationSystem = (new FirstComeApplicationSystem())->withTask($editionTask->object());

        $offer = OfferFactory::new()->withMaxParticipants(5);
        $attendances = AttendanceFactory::new()->withStatus('confirmed')->many(4)->create();
        $attendances[] = $attendance = AttendanceFactory::new()->withStatus('waitlisted')->create();

        $offer = $offer->withAttendances(array_map(fn ($a) => $a->object(), $attendances))->create();

        self::assertCount(5, $offer->object()->getAttendances());

        $applicationSystem->assignStatus($attendance->object());

        self::assertSame('confirmed', $attendance->getStatus());
    }

    public function testStatusIsConfirmedSpotLeft(): void
    {
        $editionTask = EditionTaskFactory::new()->ofTypeFirstComeApplicationSystem()->create();

        $applicationSystem = (new FirstComeApplicationSystem())->withTask($editionTask->object());

        $offer = OfferFactory::new()->withMaxParticipants(5);
        $attendances = AttendanceFactory::new()->withStatus('confirmed')->many(4)->create();
        $offer = $offer->withAttendances(array_map(fn ($a) => $a->object(), $attendances))->create();

        $attendance = new Attendance($offer->object(), new Participant());

        $applicationSystem->assignStatus($attendance);

        self::assertSame('confirmed', $attendance->getStatus());
        self::assertSame(128, $attendance->getSorting());
    }

    public function testStatusIsWaitlisted(): void
    {
        $editionTask = EditionTaskFactory::new()->ofTypeFirstComeApplicationSystem()->create();

        $applicationSystem = (new FirstComeApplicationSystem())->withTask($editionTask->object());

        $offer = OfferFactory::new()->withMaxParticipants(4);
        $attendances = AttendanceFactory::new()->withStatus('confirmed')->many(4)->create();
        $offer = $offer->withAttendances(array_map(fn ($a) => $a->object(), $attendances))->create();

        $attendance = new Attendance($offer->object(), new Participant());

        $applicationSystem->assignStatus($attendance);

        self::assertSame('waitlisted', $attendance->getStatus());
        self::assertSame(128, $attendance->getSorting());
    }

    public function testIgnoresErrorWhenCalculatingPosition(): void
    {
        $editionTask = EditionTaskFactory::new()->ofTypeFirstComeApplicationSystem()->create();

        $applicationSystem = (new FirstComeApplicationSystem())->withTask($editionTask->object());

        $offer = OfferFactory::new()->withMaxParticipants(3);

        $attendances = [];
        $attendances[] = AttendanceFactory::new()->withStatus('confirmed')->create()->object();
        $attendances[] = AttendanceFactory::new()->withStatus('confirmed')->create()->object();
        $attendances[] = AttendanceFactory::new()->withStatus('error')->create()->object();
        $attendances[] = AttendanceFactory::new()->withStatus('withdrawn')->create()->object();
        $attendances[] = $attendance = AttendanceFactory::new()->withStatus('waitlisted')->create()->object();

        $offer = $offer->withAttendances($attendances)->create();

        self::assertCount(5, $offer->object()->getAttendances());

        $applicationSystem->assignStatus($attendance);

        self::assertSame('confirmed', $attendance->getStatus());
    }
}
