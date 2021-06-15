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

use Ferienpass\CoreBundle\Helper\Dca;
use MetaModels\Factory;

$GLOBALS['TL_DCA']['tl_ferienpass_dataprocessing'] = [
    // Config
    'config' => [
        'dataContainer' => 'Table',
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => 1,
            'fields' => [
                'name',
            ],
            'flag' => 1,
        ],
        'label' => [
            'fields' => [
                'name',
                'filesystem',
            ],
            'format' => '%s <span class="tl_gray">[%s]</span>',
        ],
        'global_operations' => [
            'back' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href' => 'mod=&table=',
                'class' => 'header_back',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                .'\')) return false; Backend.getScrollOffset();"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
            'run' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['run'],
                'href' => 'key=execute',
                'icon' => 'bundles/ferienpasscore/img/play-button.svg',
            ],
        ],
    ],

    // Meta Palettes
    'metapalettes' => [
        'default' => [
            'title' => [
                'name',
                'listViewButton',
            ],
            'format' => [
                'format',
            ],
            'filesystem' => [
                'filesystem',
            ],
            'scope' => [
                'metamodel_filtering',
                'metamodel_filterparams',
                'metamodel_sortby',
                'metamodel_sortby_direction',
                'metamodel_offset',
                'metamodel_limit',
                'static_dirs',
            ],
        ],
    ],
    // Meta Sub Palettes
    'metasubpalettes' => [
        'combine_variants' => [
        ],
    ],
    // Meta SubSelect Palettes
    'metasubselectpalettes' => [
        'format' => [
            'pdf' => [
                'metamodel_view',
            ],
            'xml' => [
                'metamodel_view',
                'xml_single_file',
                'combine_variants',
                'variant_delimiters',
            ],
            'ical' => [
                'ical_fields',
            ],
            'xlsx' => [
                'metamodel_view',
            ],
        ],
        'filesystem' => [
            'local' => [
                'export_file_name',
                'path_prefix',
                'sync',
            ],
            'sendToBrowser' => [
                'export_file_name',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => 'int(10) unsigned NOT NULL default \'0\'',
        ],
        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['name'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'maxlength' => 255,
                'tl_class' => 'w50',
            ],
            'sql' => 'varchar(255) NOT NULL default \'\'',
        ],
        'listViewButton' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['listViewButton'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12',
            ],
            'sql' => 'char(1) NOT NULL default \'\'',
        ],
        'format' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['format'],
            'exclude' => true,
            'inputType' => 'select',
            'default' => 'pdf',
            'options' => [
                'pdf',
                'xml',
                'ical',
                'xlsx',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['format_options'],
            'eval' => [
                'submitOnChange' => true,
                'includeBlankOption' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'sql' => 'varchar(64) NOT NULL default \'\'',
        ],
        'metamodel_view' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_view'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => [Dca::class, 'getOffersMetaModelRenderSettings'],
            'eval' => [
                'includeBlankOption' => true,
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'sql' => 'int(10) NOT NULL default \'0\'',
        ],
        'metamodel_filtering' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filtering'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => [
                'includeBlankOption' => true,
                'submitOnChange' => true,
                'tl_class' => 'w50',
            ],
            'sql' => 'int(10) NOT NULL default \'0\'',
        ],
        'metamodel_filterparams' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filterparams'],
            'exclude' => true,
            'inputType' => 'mm_subdca',
            'eval' => [
                'tl_class' => 'clr m12',
            ],
            'sql' => 'longblob NULL',
        ],
        'metamodel_sortby' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => [
                'tl_class' => 'w50',
                'includeBlankOption' => true,
            ],
            'sql' => 'varchar(64) NOT NULL default \'0\'',
        ],
        'metamodel_limit' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_limit'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50',
            ],
            'sql' => 'int(10) NOT NULL default \'0\'',
        ],
        'metamodel_offset' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_offset'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'tl_class' => 'w50',
            ],
            'sql' => 'int(10) NOT NULL default \'0\'',
        ],
        'metamodel_sortby_direction' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby_direction'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => [
                'ASC',
                'DESC',
            ],
            'default' => 'ASC',
            'eval' => [
                'tl_class' => 'w50',
            ],
            'sql' => 'varchar(4) NOT NULL default \'\'',
        ],
        'filesystem' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem'],
            'exclude' => true,
            'inputType' => 'select',
            'options' => [
                'local',
                'sendToBrowser',
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem_options'],
            'eval' => [
                'submitOnChange' => true,
                'tl_class' => 'w50 clr',
            ],
            'sql' => 'varchar(64) NOT NULL default \'\'',
        ],
        'static_dirs' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['static_dirs'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'multiple' => 'true',
                'fieldType' => 'checkbox',
                'files' => false,
                'tl_class' => 'clr',
            ],
            'sql' => 'blob NULL',
        ],
        'combine_variants' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['combine_variants'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12',
                'submitOnChange' => true,
            ],
            'sql' => 'char(1) NOT NULL default \'\'',
        ],
        'variant_delimiters' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['variant_delimiters'],
            'exclude' => true,
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'columnFields' => [
                    'metamodel_attribute' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_attribute'],
                        'inputType' => 'conditionalselect',
                        'options_callback' => function () {
                            /** @var Factory $factory */
                            $factory = \Contao\System::getContainer()->get('metamodels.factory');
                            $metaModel = $factory->getMetaModel('mm_ferienpass');
                            if (null === $metaModel) {
                                return [];
                            }

                            $return = [];
                            foreach ($metaModel->getAttributes() as $attrName => $attribute) {
                                $return[$attrName] = $attribute->getName();
                            }

                            return $return;
                        },
                        'eval' => [
                            'condition' => 'mm_ferienpass',
                            'chosen' => true,
                            'includeBlankOption' => true,
                            'style' => 'width:200px',
                        ],
                    ],
                    'delimiter' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['delimiter'],
                        'inputType' => 'text',
                        'eval' => [
                            'style' => 'width:50px',
                        ],
                    ],
                    'newline' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline'],
                        'inputType' => 'checkbox',
                    ],
                    'newline_position' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline_position'],
                        'reference' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline_positions'],
                        'inputType' => 'select',
                        'default' => 'after',
                        'options' => [
                            'before',
                            'after',
                        ],
                        'eval' => [
                            'style' => 'width:150px',
                        ],
                    ],
                ],
                'tl_class' => 'clr',
            ],
            'sql' => 'text NULL',
        ],
        'xml_single_file' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['xml_single_file'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 m12',
            ],
            'sql' => 'char(1) NOT NULL default \'\'',
        ],
        'export_file_name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['export_file_name'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'sql' => 'varchar(255) NOT NULL default \'\'',
        ],
        'path_prefix' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['path_prefix'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => [
                'trailingSlash' => false,
                'tl_class' => 'w50',
            ],
            'sql' => 'varchar(255) NOT NULL default \'\'',
        ],
        'sync' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['sync'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'submitOnChange' => 'true',
                'tl_class' => 'w50 m12',
            ],
            'sql' => 'char(1) NOT NULL default \'\'',
        ],
        'ical_fields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['ical_fields'],
            'exclude' => true,
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'columnFields' => [
                    'ical_field' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['ical_field'],
                        'inputType' => 'select',
                        'options' => [
                            'summary',
                            'description',
                            'location',
                        ],
                        'eval' => [
                            'style' => 'width:250px',
                            'chosen' => true,
                        ],
                    ],
                    'metamodel_attribute' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_attribute'],
                        'inputType' => 'conditionalselect',
                        'eval' => [
                            'condition' => 'mm_ferienpass',
                            'chosen' => true,
                            'style' => 'width:250px',
                        ],
                    ],
                ],
                'tl_class' => 'clr',
            ],
            'sql' => 'text NULL',
        ],
    ],
];
