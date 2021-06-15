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

$GLOBALS['TL_DCA']['Edition'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['EditionTask'],
    ],
    'list' => [
        'sorting' => [
            'mode' => 1,
            'flag' => 1,
            'fields' => ['name'],
            'panelLayout' => 'limit',
        ],
        'label' => [
            'fields' => ['name'],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'tasks' => [
                'href' => 'table=EditionTask',
                'icon' => 'bundles/ferienpasscore/img/tasks.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => sprintf('onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"', $GLOBALS['TL_LANG']['MSC']['deleteConfirm']),
            ],

            'stats' => [
                'href' => 'key=stats',
                'icon' => 'bundles/ferienpasscore/img/stats.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{title_legend},name,alias;{configuration_legend:hide},listPage',
    ],
    'fields' => [
        'name' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
        ],
        'alias' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50',
                'unique' => true,
            ],
        ],
        'listPage' => [
            'exclude' => true,
            'inputType' => 'pageTree',
            'eval' => ['fieldType' => 'radio'],
            'save_callback' => [fn ($v) => (int) $v],
        ],
    ],
];
