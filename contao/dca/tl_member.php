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

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_member']['list']['label']['fields'] =
    array_diff($GLOBALS['TL_DCA']['tl_member']['list']['label']['fields'], ['dateAdded', 'username']);
$GLOBALS['TL_DCA']['tl_member']['list']['label']['fields'][] = 'email';

unset($GLOBALS['TL_DCA']['tl_member']['fields']['assignDir']);

$GLOBALS['TL_DCA']['tl_member']['fields']['country']['eval']['mandatory'] = false;

$GLOBALS['TL_DCA']['tl_member']['fields']['country']['filter'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['language']['filter'] = false;

(new PaletteManipulator())
    ->addField('hosts', 'groups', PaletteManipulator::POSITION_AFTER)
    ->addField('public_fields', 'contact_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member')
    ;

$GLOBALS['TL_DCA']['tl_member']['fields']['hosts'] = [
    'inputType' => 'select',
    'filter' => true,
    'eval' => [
        'multiple' => true,
        'tl_class' => 'clr',
        'chosen' => true,
    ],
    'foreignKey' => 'Host.name',
    'relation' => [
        'type' => 'haste-ManyToMany',
        'load' => 'lazy',
        'table' => 'Host',
        'field' => 'id',
        'referenceColumn' => 'member_id',
        'fieldColumn' => 'host_id',
        'relationTable' => 'HostMemberAssociation',
        'skipInstall' => true,
    ],
];

$GLOBALS['TL_DCA']['tl_member']['fields']['public_fields'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => ['firstname', 'lastname', 'email', 'phone', 'mobile'],
    'eval' => ['multiple' => true, 'csv' => ',', 'tl_class' => 'clr'],
    'sql' => ['type' => 'string', 'default' => ''],
];
