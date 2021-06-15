<?php

/**
 * ImageCopyright for Contao Open Source CMS.
 *
 * @copyright   2016 – 2020 Tastaturberuf <tastaturberuf.de>
 * @author      Daniel Jahnsmüller <tastaturberuf.de>
 * @license     LGPL-3.0-or-later
 */

declare(strict_types=1);

/*
 * This file is part of the Ferienpass package.
 *
 * (c) Richard Henkenjohann <richard@ferienpass.online>
 *
 * For more information visit the project website <https://ferienpass.online>
 * or the documentation under <https://docs.ferienpass.online>.
 */

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Files;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

class PaletteListener
{
    /**
     * @Callback(table="tl_files", target="config.onload")
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id) {
            return;
        }

        $fileExtension = pathinfo((string) $dc->id)['extension'];
        if (!\in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            return;
        }

        PaletteManipulator::create()
            ->addLegend('copyright_legend', 'meta', PaletteManipulator::POSITION_BEFORE)
            ->addField('imgCopyright', 'copyright_legend')
            ->applyToPalette('default', $dc->table)
        ;
    }
}
