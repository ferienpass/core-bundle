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

namespace Ferienpass\CoreBundle\EventListener\Backend;

use Contao\CoreBundle\Event\MenuEvent;
use Ferienpass\CoreBundle\Controller\Backend\ExportController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class BackendMenuListener
{
    public function __construct(private RouterInterface $router, private RequestStack $requestStack)
    {
    }

    public function __invoke(MenuEvent $event): void
    {
        $factory = $event->getFactory();
        $tree = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        $parentNode = $tree->getChild('ferienpass');

        $node = $factory
            ->createItem('export')
            ->setUri($this->router->generate('backend_export'))
            ->setLabel('Export')
            ->setLinkAttribute('title', 'Angebote exportieren')
            ->setCurrent(ExportController::class === $this->requestStack->getCurrentRequest()->get('_controller'));

        $parentNode->addChild($node);
    }
}
