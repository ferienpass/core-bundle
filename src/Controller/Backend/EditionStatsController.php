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
use Ferienpass\CoreBundle\Fragment\EditionStatsWidgetReference;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ferienpass/{id}/statistik", name="backend_edition_stats", requirements={"id"="\d+"})
 */
class EditionStatsController extends AbstractController
{
    public function __construct(private FragmentRegistryInterface $fragmentRegistry, private FragmentHandler $fragmentHandler)
    {
    }

    public function __invoke(int $id): Response
    {
        $this->initializeContaoFramework();

        $widgets = array_values(array_filter(
            $this->fragmentRegistry->keys(),
            fn ($key) => str_starts_with($key, EditionStatsWidgetReference::TAG_NAME.'.')
        ));

        $rendered = implode('', array_map(
            fn (string $widget): ?string => $this->fragmentHandler->render(new FragmentReference($widget, ['id' => $id]), 'forward'),
            $widgets
        ));

        $GLOBALS['TL_JAVASCRIPT']['frappe-charts'] =
            'https://cdn.jsdelivr.net/npm/frappe-charts@1.1.0/dist/frappe-charts.min.iife.js';

        return $this->render('@FerienpassCore/Backend/edition_stats.html.twig', [
            'widgets' => $rendered,
        ]);
    }
}
