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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\EditionTask;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Symfony\Contracts\Translation\TranslatorInterface;

class LabelListener
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Callback(table="EditionTask", target="list.sorting.child_record")
     */
    public function labelCallback(array $row): string
    {
        if ('custom' === $row['type']) {
            return sprintf('%s <span class="tl_gray">(%s)</span>', $row['title'], 'Benutzerdefiniert');
        }

        if ('application_system' === $row['type']) {
            return sprintf(
                '%s <span class="tl_gray">(%s)</span>',
                $this->translator->trans('MSC.application_system.'.$row['application_system'], [], 'contao_default'),
                'Anmeldeverfahren'
            );
        }

        return $this->translator->trans('EditionTask.type_options.'.$row['type'], [], 'contao_EditionTask');
    }
}
