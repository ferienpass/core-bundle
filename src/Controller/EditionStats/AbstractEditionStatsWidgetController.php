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

namespace Ferienpass\CoreBundle\Controller\EditionStats;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Fragment\FragmentOptionsAwareInterface;

abstract class AbstractEditionStatsWidgetController extends AbstractController implements FragmentOptionsAwareInterface
{
    protected array $options = [];

    public function setFragmentOptions(array $options): void
    {
        $this->options = $options;
    }
}
