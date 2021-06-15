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

use Ferienpass\CoreBundle\Fragment\FragmentReference;
use Ferienpass\CoreBundle\Page\PageBuilder;

class ApplicationsPage extends AbstractContentPage
{
    protected function modifyPage(PageBuilder $pageBuilder): void
    {
        $pageBuilder->addFragment('main', new FragmentReference('ferienpass.fragment.application_list'));
    }
}
