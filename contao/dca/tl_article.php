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

$GLOBALS['TL_DCA']['tl_article']['palettes']['default'] = str_replace(
    ',stop',
    ',stop,ferienpass_task_condition,ferienpass_task_condition_inverted',
    $GLOBALS['TL_DCA']['tl_article']['palettes']['default']
);

$GLOBALS['TL_DCA']['tl_article']['fields']['ferienpass_task_condition'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_article']['ferienpass_task_condition'],
    'reference' => &$GLOBALS['TL_LANG']['EditionTask']['type_options'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'holiday',
        'host_editing_stage',
        'application_system',
        'allocation',
        'pay_days',
        'publish_lists',
        'show_offers',
    ],
    'eval' => [
        'tl_class' => 'clr w50',
        'includeBlankOption' => true,
    ],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_article']['fields']['ferienpass_task_condition_inverted'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_article']['ferienpass_task_condition_inverted'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50 m12',
    ],
    'sql' => "char(1) NOT NULL default ''",
];
