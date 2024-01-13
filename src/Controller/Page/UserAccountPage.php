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

use Contao\CoreBundle\Exception\PageNotFoundException;
use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Fragment\FragmentReference;
use Ferienpass\CoreBundle\UserAccount\UserAccountFragments;
use Ferienpass\CoreBundle\UserAccount\UserAccountFragmentValueHolder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAccountPage extends AbstractController
{
    public function __construct(private readonly UserAccountFragments $fragments)
    {
    }

    public function __invoke(string $alias, Request $request): Response
    {
        $this->initializeContaoFramework();

        $keys = array_keys(array_filter($this->fragments->all(), fn (UserAccountFragmentValueHolder $vh) => $alias === $vh->getAlias()));

        if (false === $key = current($keys)) {
            throw new PageNotFoundException();
        }

        $this->checkToken();

        return $this->createPageBuilder($request->attributes->get('pageModel'))
            ->addFragment('main', new FragmentReference('ferienpass.fragment.'.$key))
            ->getResponse()
        ;
    }
}
