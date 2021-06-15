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

namespace Ferienpass\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Fragment\FragmentHandler;
use Contao\CoreBundle\Fragment\FragmentRegistryInterface;
use Contao\CoreBundle\Fragment\Reference\FragmentReference;
use Ferienpass\CoreBundle\Fragment\DashboardWidgetReference;
use Symfony\Component\HttpFoundation\Response;

final class DashboardController extends AbstractController
{
    private FragmentRegistryInterface $fragmentRegistry;
    private FragmentHandler $fragmentHandler;

    public function __construct(FragmentRegistryInterface $fragmentRegistry, FragmentHandler $fragmentHandler)
    {
        $this->fragmentRegistry = $fragmentRegistry;
        $this->fragmentHandler = $fragmentHandler;
    }

    public function __invoke(): Response
    {
        $this->initializeContaoFramework();

        $widgets = array_values(array_filter(
            $this->fragmentRegistry->keys(),
            fn ($key) => 0 === strpos($key, DashboardWidgetReference::TAG_NAME.'.')
        ));

        $dashboard = implode('', array_map(
            fn (string $widget): ?string => $this->fragmentHandler->render(new FragmentReference($widget), 'forward'),
            $widgets
        ));

        return $this->render('@FerienpassCore/Backend/dashboard.html.twig', [
            'dashboard' => $dashboard,
        ]);
    }
}
