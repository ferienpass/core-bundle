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

namespace Ferienpass\CoreBundle\Tests\Cron;

use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Ferienpass\CoreBundle\Cron\SendRemindersListener;
use Ferienpass\CoreBundle\Fixtures\Factory\AttendanceFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Foundry\Test\Factories;

class SendRemindersListenerTest extends KernelTestCase
{
    use Factories;

    public function testSomething(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $messageBus->expects($this->atLeastOnce())->method('dispatch');

        $listener = new SendRemindersListener(AttendanceFactory::repository(), $messageBus);

        $listener('cli');
    }

    protected static function createKernel(array $options = [])
    {
        $kernel = new ContaoKernel('test', true);
        $kernel::setProjectDir('/Users/richard/Sites/ferienpass/core-bundle/tests/Functional/');

        Debug::enable();

        return $kernel;
    }
}
