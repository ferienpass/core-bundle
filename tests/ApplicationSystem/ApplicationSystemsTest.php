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

use Contao\BackendUser;
use Contao\FrontendUser;
use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystems;
use Ferienpass\CoreBundle\ApplicationSystem\FirstComeApplicationSystem;
use Ferienpass\CoreBundle\Exception\AmbiguousApplicationSystemException;
use Ferienpass\CoreBundle\Fixtures\Factory\EditionFactory;
use Ferienpass\CoreBundle\Fixtures\Factory\EditionTaskFactory;
use Ferienpass\CoreBundle\Fixtures\Factory\OfferFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Security;
use Zenstruck\Foundry\Test\Factories;

class ApplicationSystemsTest extends TestCase
{
    use Factories;

    public function testGetsLotPerDefault(): void
    {
        $offer = OfferFactory::createOne();

        $security = $this->mockSecurity();

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with('ferienpass.application_system.lot')
        ;

        $applicationSystems = new ApplicationSystems($security);
        $applicationSystems->setContainer($container);

        $applicationSystems->findApplicationSystem($offer->object());
    }

    public function testGetsAdminForBackendUsers(): void
    {
        $offer = OfferFactory::createOne();

        $security = $this->mockSecurity(true);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with('ferienpass.application_system.admin')
        ;

        $applicationSystems = new ApplicationSystems($security);
        $applicationSystems->setContainer($container);

        $applicationSystems->findApplicationSystem($offer->object());
    }

    public function testGetsAdminForHosts(): void
    {
        $offer = OfferFactory::createOne();

        $security = $this->mockSecurity(false, true);

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with('ferienpass.application_system.admin')
        ;

        $applicationSystems = new ApplicationSystems($security);
        $applicationSystems->setContainer($container);

        $applicationSystems->findApplicationSystem($offer->object());
    }

    public function testReturnsNullIfNoTasks(): void
    {
        $edition = EditionFactory::new()->create();
        $offer = OfferFactory::new()->withEdition($edition)->create();

        $security = $this->mockSecurity();

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->never())
            ->method('get')
        ;

        $applicationSystems = new ApplicationSystems($security);
        $applicationSystems->setContainer($container);

        $applicationSystem = $applicationSystems->findApplicationSystem($offer->object());

        self::assertNull($applicationSystem);
    }

    public function testThrowsExceptionIfMultipleApplicationSystems(): void
    {
        $edition = EditionFactory::new();

        $tasks = [];
        $tasks[] = EditionTaskFactory::new()->withEdition($edition)->ofTypeFirstComeApplicationSystem()->create()->object();
        $tasks[] = EditionTaskFactory::new()->withEdition($edition)->ofTypeLotApplicationSystem()->create()->object();

        $edition = $edition->withTasks($tasks)->create();

        $offer = OfferFactory::new()->withEdition($edition)->create();

        $security = $this->mockSecurity();

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->never())
            ->method('get')
        ;

        $this->expectException(AmbiguousApplicationSystemException::class);

        $applicationSystems = new ApplicationSystems($security);
        $applicationSystems->setContainer($container);

        $applicationSystems->findApplicationSystem($offer->object());
    }

    public function testReturnsCurrentFromTask(): void
    {
        $edition = EditionFactory::new();

        $tasks = [];
        $tasks[] = EditionTaskFactory::new()->withEdition($edition)->ofTypeFirstComeApplicationSystem()->create()->object();

        $edition = $edition->withTasks($tasks)->create();

        $offer = OfferFactory::new()->withEdition($edition)->create();

        $security = $this->mockSecurity();

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->once())
            ->method('get')
            ->with('ferienpass.application_system.firstcome')
            ->willReturn(new FirstComeApplicationSystem())
        ;

        $applicationSystems = new ApplicationSystems($security);
        $applicationSystems->setContainer($container);

        $applicationSystems->findApplicationSystem($offer->object());
    }

    public function testReturnsNoneIfTaskInPast(): void
    {
        $edition = EditionFactory::new();

        $tasks = [];
        $tasks[] = EditionTaskFactory::new()->withEdition($edition)->ofTypeFirstComeApplicationSystem()->isInPast()->create()->object();

        $edition = $edition->withTasks($tasks)->create();

        $offer = OfferFactory::new()->withEdition($edition)->create();

        $security = $this->mockSecurity();

        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->never())
            ->method('get')
        ;

        $applicationSystems = new ApplicationSystems($security);
        $applicationSystems->setContainer($container);

        $applicationSystem = $applicationSystems->findApplicationSystem($offer->object());

        self::assertNull($applicationSystem);
    }

    private function mockSecurity($isBackendUser = false, $isHost = false)
    {
        $security = $this->createMock(Security::class);

        if ($isBackendUser) {
            $security
            ->method('getUser')
            ->willReturn($this->createMock(BackendUser::class));

            return $security;
        }

        $user = $this->createMock(FrontendUser::class);

        if ($isHost) {
            $user->method('isMemberOf')
                ->with(1)
                ->willReturn(true);
        }

        $security
            ->method('getUser')
            ->willReturn($user);

        return $security;
    }
}
