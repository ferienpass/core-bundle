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

namespace Ferienpass\CoreBundle\Controller\Page;

use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Fragment\FragmentReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAccountPage extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $this->initializeContaoFramework();

        $this->checkToken();

        return $this->createPageBuilder($request->get('pageModel'))
            ->addFragment('main', new FragmentReference('ferienpass.fragment.change_password'))
            ->addFragment('main', new FragmentReference('ferienpass.fragment.close_account'))
            ->getResponse()
            ;
    }
}
