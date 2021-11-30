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

foreach ($GLOBALS['TL_DCA']['tl_content']['palettes'] as $k => $palette) {
    if ('__selector__' === $k) {
        continue;
    }

    $GLOBALS['TL_DCA']['tl_content']['palettes'][$k] = str_replace(
        ',stop',
        ',stop,ferienpass_task_condition,ferienpass_task_condition_inverted',
        $GLOBALS['TL_DCA']['tl_content']['palettes'][$k]
    );
}

$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'block_layout';
$GLOBALS['TL_DCA']['tl_content']['palettes']['text_block'] =
    '{type_legend},type;{template_legend},block_layout';
$GLOBALS['TL_DCA']['tl_content']['palettes']['countdown'] = '{type_legend},type;{text_legend},headline';
$GLOBALS['TL_DCA']['tl_content']['palettes']['greeting_with_picture'] =
    '{type_legend},type;{text_legend},headline,text;{image_legend},singleSRC';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['block_layout_w-full'] = 'headline,text';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['block_layout_max-w'] = 'headline,text';
$GLOBALS['TL_DCA']['tl_content']['subpalettes']['block_layout_max-w+center'] = 'headline,text';

$GLOBALS['TL_DCA']['tl_content']['palettes']['hyperlink_button'] =
    '{type_legend},type;{link_legend},url,target,linkTitle,titleText;{template_legend},buttonStyle';

$GLOBALS['TL_DCA']['tl_content']['palettes']['contact'] =
    '{type_legend},type,headline;{text_legend},text,address,email,phone,form';

$GLOBALS['TL_DCA']['tl_content']['fields']['address'] = [
    'exclude' => true,
    'inputType' => 'textarea',
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['email'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'email'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['phone'] = [
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['rgxp' => 'phone'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['block_layout'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'w-full',
        'max-w',
        'max-w+center',
        'max-w+offset',
    ],
    'eval' => [
        'submitOnChange' => true,
        'includeBlankOption' => true,
    ],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['buttonStyle'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => [
        'primary+xs',
        'primary+sm',
        'primary',
        'primary+lg',
        'primary+xl',
        'default+xs',
        'default+sm',
        'default',
        'default+lg',
        'default+xl',
    ],
    'default' => 'default',
    'eval' => [
        'mandatory' => true,
    ],
    'sql' => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['palettes']['pdf_proof'] =
    '{type_legend},type,headline;{list_legend},listitems;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['fields']['ferienpass_task_condition'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['ferienpass_task_condition'],
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

$GLOBALS['TL_DCA']['tl_content']['fields']['ferienpass_task_condition_inverted'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['ferienpass_task_condition_inverted'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50 m12',
    ],
    'sql' => "char(1) NOT NULL default ''",
];
