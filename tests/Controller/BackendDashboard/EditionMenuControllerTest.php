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

namespace Ferienpass\CoreBundle\Tests\Controller\BackendDashboard;

use Ferienpass\CoreBundle\Controller\BackendDashboard\EditionMenuController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;

class EditionMenuControllerTest extends WebTestCase
{
    use ResetDatabase;

    public function testIndex()
    {
        $controller = new EditionMenuController();
    }
}
