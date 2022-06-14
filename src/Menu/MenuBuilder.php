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
use Ferienpass\CoreBundle\UserAccount\UserAccountFragments;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;

class MenuBuilder
{
    public function __construct(private FactoryInterface $factory, private LogoutUrlGenerator $logoutUrlGenerator, private RequestStack $requestStack, private UserAccountFragments $userAccountFragments)
    {
    }

    public function userNavigation(): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Teilnehmer:innen', [
            'route' => 'user_account',
            'routeParameters' => ['alias' => 'teilnehmer'],
            'current' => $this->isCurrent('user_account', 'participants'),
            'extras' => ['icon' => 'user-group-solid'],
        ]);

        $menu->addChild('Nutzer-Account', [
            'route' => 'user_account',
            'routeParameters' => ['alias' => 'persönliche-daten'],
            'current' => $this->isCurrent('user_account', 'personal_data'),
            'extras' => ['icon' => 'lock-closed-solid'],
        ]);

        $menu->addChild('Abmelden', [
            'uri' => $this->logoutUrlGenerator->getLogoutUrl('contao_frontend'),
            'extras' => ['icon' => 'logout-solid'],
        ]);

        return $menu;
    }

    public function userAccountNavigation(): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        foreach ($this->userAccountFragments->all() as $valueHolder) {
            $menu->addChild($valueHolder->getKey(), [
                'route' => 'user_account',
                'routeParameters' => ['alias' => $valueHolder->getAlias()],
                'current' => $this->isCurrent('user_account', $valueHolder->getAlias()),
                'extras' => ['icon' => $valueHolder->getIcon()],
            ]);
        }

        return $menu;
    }

    private function isCurrent(string $type, string $fragment = null): bool
    {
        $request = $this->requestStack->getMainRequest();
        if (null === $request) {
            return false;
        }

        $pageModel = $request->attributes->get('pageModel');
        if (!$pageModel instanceof PageModel) {
            return false;
        }

        if (null === $fragment) {
            return $type === $pageModel->type;
        }

        return $type === $pageModel->type && $fragment === $request->attributes->get('alias');
    }
}
