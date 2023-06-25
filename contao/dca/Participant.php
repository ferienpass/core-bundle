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

$GLOBALS['TL_DCA']['Participant'] = [
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'enableVersioning' => true,
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['lastname'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['lastname', 'dateOfBirth', 'phone', 'email'],
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
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => sprintf('onclick="if(!confirm(\'%s\'))return false;Backend.getScrollOffset()"', $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null),
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
            'attendances' => [
                'href' => 'table=Attendance',
                'icon' => 'bundles/ferienpasscore/img/attendances.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{name_legend},firstname,lastname,member_id,dateOfBirth;{contact_legend:hide},phone,mobile,email',
    ],
    'fields' => [
        'firstname' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
        ],
        'lastname' => [
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
        ],
        'alias' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
        ],
        'member_id' => [
            'exclude' => true,
            'inputType' => 'select',
            'filter' => true,
            'foreignKey' => "tl_member.CONCAT(firstname, ' ', lastname)",
            'eval' => ['tl_class' => 'w50', 'chosen' => true],
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'dateOfBirth' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
        ],
        'phone' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 64, 'rgxp' => 'phone', 'decodeEntities' => true, 'tl_class' => 'w50'],
        ],
        'mobile' => [
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
    ],
];
