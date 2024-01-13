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

namespace Ferienpass\CoreBundle\Twig\Extension;

use Contao\FilesModel;
use Contao\StringUtil;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class StringUtilExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('encodeEmail', $this->encodeEmail(...), ['is_safe' => ['html']]),
            new TwigFilter('filesModel', $this->filesModel(...)),
        ];
    }

    public function encodeEmail(?string $string): string
    {
        return StringUtil::encodeEmail((string) $string);
    }

    public function filesModel(?string $id): ?FilesModel
    {
        return FilesModel::findByPk($id);
    }
}
