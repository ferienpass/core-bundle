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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Host;

use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Ferienpass\CoreBundle\Entity\Host;

class LabelListener
{
    public function __construct(private Studio $studio)
    {
    }

    /**
     * @Callback(table="Host", target="list.label.label")
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $labels)
    {
        if ($row['logo']) {
            try {
                $image = $this->studio->createFigureBuilder()->fromUuid($row['logo'])->setSize([32, 32, 'crop'])->build()->getImage();

                $labels[0] = sprintf('<img src="%s" width="16" height="16" alt="">', $image->getImageSrc());
            } catch (\InvalidArgumentException) {
                $labels[0] = 'ERR';
            }
        }

        return $labels;
    }
}
