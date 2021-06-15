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

namespace Ferienpass\CoreBundle\Tests\Controller\Fragment;

use Contao\ManagerBundle\HttpKernel\ContaoKernel;
use Contao\TestCase\FunctionalTestCase;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use Zenstruck\Foundry\Test\ResetDatabase;

class AccountDeletedControllerTest extends FunctionalTestCase
{
    use ResetDatabase;

    public function testIndex()
    {
        $client = static::createClient();

        $request = $this->createMock(Request::class);
        $request->method('getBasePath')->willReturn('');
        $request->method('getScriptName')->willReturn('index.php');
        $request = new Request();

        $requestStack = self::$container->get('request_stack');
        $requestStack->push($request);

        $client->request('GET', '/fragment/account_deleted');

        $this->assertResponseIsSuccessful();
    }

    protected static function createKernel(array $options = [])
    {
        $kernel = new ContaoKernel('test', true);
        $kernel::setProjectDir('/Users/richard/Sites/ferienpass/core-bundle/tests/Functional/');

        Debug::enable();

        return $kernel;
    }
}
