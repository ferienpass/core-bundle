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

use Contao\PageModel;
use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Fragment\FragmentReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HostDetailsPage extends AbstractController
{
    public function __invoke(Host $host, Request $request): Response
    {
        $this->initializeContaoFramework();

        $pageModel = $request->attributes->get('pageModel');
        if ($pageModel instanceof PageModel) {
            $pageModel->title = $host->getName();
        }

        return $this->createPageBuilder($request->get('pageModel'))
            ->addFragment('main', new FragmentReference('ferienpass.fragment.host_details', ['host' => $host]))
            ->getResponse()
            ;
    }
}
