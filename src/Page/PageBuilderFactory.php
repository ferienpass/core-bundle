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

namespace Ferienpass\CoreBundle\Page;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageBuilderFactory
{
    public function __construct(private FragmentHandler $fragmentHandler, private TranslatorInterface $translator, private ContaoFramework $framework, private RequestStack $requestStack)
    {
    }

    public function create(PageModel $pageModel = null): PageBuilder
    {
        if (null === $pageModel) {
            $pageModel = PageModel::findPublishedRootPages(['column' => ["tl_page.type='root'", "dns NOT LIKE 'veranstalter%%'"]])[0];
        }

        return new PageBuilder($pageModel, $this->fragmentHandler, $this->translator, $this->framework, $this->requestStack);
    }
}
