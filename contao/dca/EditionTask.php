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

$GLOBALS['TL_DCA']['EditionTask'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'Edition',
        'switchToEdit' => false,
        'enableVersioning' => false,
    ],
    'list' => [
        'sorting' => [
            'mode' => 4,
            'fields' => ['sorting'],
            'headerFields' => ['name'],
            'flag' => 1,
            'panelLayout' => 'sort,search;limit',
        ],
        'label' => [
            'fields' => [
                'type',
                'periodBegin',
                'periodEnd',
            ],
            'showColumns' => true,
        ],
        'global_operations' => [
            'back' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href' => 'mod=&table=',
                'class' => 'header_back',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['EditionTask']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['EditionTask']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'Löschen?\')) return false; Backend.getScrollOffset();"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['EditionTask']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'application_system'],
        'default' => '{title_legend},type;{period_legend},periodBegin,periodEnd',
    ],
    'subpalettes' => [
        'type_custom' => 'title,description',
        'type_application_system' => 'application_system',
        'application_system_lot' => 'max_applications,hide_status',
        'application_system_firstcome' => 'max_applications_day',
    ],
    'fields' => [
        'sorting' => [
            'sorting' => true,
            'flag' => 11,
        ],
        'type' => [
            'exclude' => true,
            'reference' => &$GLOBALS['TL_LANG']['EditionTask']['type_options'],
            'inputType' => 'select',
            'options' => [
                'holiday',
                'host_editing_stage',
                'application_system',
                'allocation',
                'pay_days',
                'publish_lists',
                'show_offers',
                'custom',
            ],
            'eval' => [
                'mandatory' => true,
                'submitOnChange' => true,
                'includeBlankOption' => true,
            ],
        ],
        'title' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
            ],
        ],
        'application_system' => [
            'exclude' => true,
            'reference' => &$GLOBALS['TL_LANG']['MSC']['application_system'],
            'inputType' => 'select',
            'options' => ['firstcome', 'lot'],
            'eval' => [
                'mandatory' => true,
                'submitOnChange' => true,
                'includeBlankOption' => true,
            ],
        ],
        'hide_status' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12',
            ],
            'save_callback' => [fn ($val) => (int) $val],
        ],
        'max_applications' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'rgxp' => 'numeric'],
            'save_callback' => [fn ($val) => (int) $val],
        ],
        'max_applications_day' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'rgxp' => 'numeric'],
            'save_callback' => [fn ($val) => (int) $val],
        ],
        'periodBegin' => [
            'exclude' => true,
            'inputType' => 'text',
            'sorting' => true,
            'flag' => 9,
            'eval' => [
                'rgxp' => 'datim',
                'datepicker' => true,
                'mandatory' => true,
                'tl_class' => 'w50 wizard',
            ],
            'load_callback' => [fn ($v) => strtotime($v)],
            'save_callback' => [fn ($v) => $v ? date('Y-m-d H:i:s', $v) : null],
        ],
        'periodEnd' => [
            'exclude' => true,
            'inputType' => 'text',
            'sorting' => true,
            'flag' => 9,
            'eval' => [
                'rgxp' => 'datim',
                'datepicker' => true,
                'mandatory' => true,
                'tl_class' => 'w50 wizard',
            ],
            'load_callback' => [fn ($v) => strtotime($v)],
            'save_callback' => [fn ($v) => $v ? date('Y-m-d H:i:s', $v) : null],
        ],
        'color' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'colorpicker' => true,
                'isHexColor' => true,
                'decodeEntities' => true,
                'tl_class' => 'w50 wizard',
            ],
        ],
        'description' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => [
                'tl_class' => 'clr long',
            ],
        ],
    ],
];
