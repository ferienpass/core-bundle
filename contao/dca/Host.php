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

$GLOBALS['TL_DCA']['Host'] = [
    'config' => [
        'dataContainer' => 'Table',
        'enableVersioning' => true,
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['name'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['logo', 'name', 'email', 'website', 'phone'],
            'showColumns' => true,
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg',
            ],
            'copy' => [
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => sprintf('onclick="if(!confirm(\'%s\'))return false;Backend.getScrollOffset()"', $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null),
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},name,alias;{address_legend},street,postal,city;{contact_legend},phone,fax,email,website;{logo_legend},logo;{text_legend},text;{active_legend},active',
    ],
    'fields' => [
        'name' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        ],
        'alias' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
        ],
        'street' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
        ],
        'postal' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 32, 'tl_class' => 'w50'],
        ],
        'city' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
        ],
        'phone' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
        ],
        'fax' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'email' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'rgxp' => 'email', 'tl_class' => 'w50'],
        ],
        'website' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => \Contao\CoreBundle\EventListener\Widget\HttpUrlListener::RGXP_NAME, 'maxlength' => 255, 'tl_class' => 'w50'],
        ],
        'logo' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['filesOnly' => true, 'fieldType' => 'radio', 'extensions' => 'jpg,jpeg,png,gif,svg,pdf', 'mandatory' => false, 'tl_class' => 'clr'],
        ],
        'text' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr'],
        ],
        'active' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
        ],
    ],
];
