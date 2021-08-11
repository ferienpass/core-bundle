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

namespace Ferienpass\CoreBundle\Fragment;

class EditionStatsWidgetReference extends \Contao\CoreBundle\Fragment\Reference\FragmentReference
{
    public const TAG_NAME = 'contao.edition_stats_widget';

    public function __construct(string $name)
    {
        parent::__construct(self::TAG_NAME.'.'.$name);

        $this->setBackendScope();
    }
}
