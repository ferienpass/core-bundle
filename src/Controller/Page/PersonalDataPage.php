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

class PersonalDataPage extends AbstractContentPage
{
    protected function modifyPage(PageBuilder $pageBuilder): void
    {
        $this->checkToken();

        $pageBuilder
            ->addFragment('main', new FragmentReference('ferienpass.fragment.participants'))
            ->addFragment('main', new FragmentReference('ferienpass.fragment.personal_data'))
        ;
    }
}
