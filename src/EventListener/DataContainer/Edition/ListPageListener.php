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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Edition;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\PageModel;

class ListPageListener
{
    /**
     * @Callback(table="Edition", target="fields.listPage.load")
     */
    public function onLoadCallback(?string $value)
    {
        if (empty($value)) {
            $page = PageModel::findOneBy('type', 'offer_list');
            if (null !== $page) {
                return $page->id;
            }
        }

        return $value;
    }

    /**
     * @Callback(table="Edition", target="fields.listPage.save")
     */
    public function onSaveCallback(?string $value): ?int
    {
        if (null === $value) {
            return null;
        }

        $page = PageModel::findByPk($value);
        if (null !== $page && 'offer_list' !== $page->type) {
            throw new \LogicException('Selected page has invalid page type');
        }

        return (int) $value;
    }
}
