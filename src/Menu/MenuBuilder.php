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

namespace Ferienpass\CoreBundle\Menu;

use Contao\PageModel;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class MenuBuilder
{
    private FactoryInterface $factory;
    private LogoutUrlGenerator $logoutUrlGenerator;
    private RequestStack $requestStack;

    public function __construct(FactoryInterface $factory, LogoutUrlGenerator $logoutUrlGenerator, RequestStack $requestStack)
    {
        $this->factory = $factory;
        $this->logoutUrlGenerator = $logoutUrlGenerator;
        $this->requestStack = $requestStack;
    }

    public function userNavigation(): ItemInterface
    {
        $menu = $this->factory->createItem('root');

//        $menu->addChild('Benachrichtigungen', [
//            'route' => 'notifications',
//            'current' => $this->isCurrent('notifications'),
//            'extras' => ['icon' => 'bell-solid'],
//        ]);

        $menu->addChild('Nutzer-Account', [
            'route' => 'user_account',
            'current' => $this->isCurrent('user_account'),
            'extras' => ['icon' => 'lock-closed-solid'],
        ]);

        $menu->addChild('Abmelden', [
            'uri' => $this->logoutUrlGenerator->getLogoutUrl('contao_frontend'),
            'extras' => ['icon' => 'logout-solid'],
        ]);

        return $menu;
    }

    private function isCurrent(string $type): bool
    {
        $request = $this->requestStack->getMasterRequest();
        if (null === $request) {
            return false;
        }

        $pageModel = $request->attributes->get('pageModel');
        if (!$pageModel instanceof PageModel) {
            return false;
        }

        return $type === $pageModel->type;
    }
}
