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

namespace Ferienpass\CoreBundle\EventListener\Routing;

use Contao\PageModel;
use Symfony\Cmf\Component\Routing\Event\RouterGenerateEvent;

/**
 * Generates the URL for a Contao page by its page type.
 */
class PageUrlGeneratorListener
{
    public function __invoke(RouterGenerateEvent $event)
    {
        if (null === $page = PageModel::findOneBy(["type=? AND published='1'"], [$event->getRoute()])) {
            return;
        }

        $event->setRoute('tl_page.'.$page->id);
    }
}
