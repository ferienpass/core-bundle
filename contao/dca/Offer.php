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

$GLOBALS['TL_DCA']['Offer'] = [
    'config' => [
        'dataContainer' => \Contao\DC_Table::class,
        'enableVersioning' => true,
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['name'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['edition', 'hosts', 'name', 'dates', 'onlineApplication'],
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
            'toggle' => [
                'attributes' => 'onclick="Backend.getScrollOffset();"',
                'haste_ajax_operation' => ['field' => 'published', 'options' => [[
                    'value' => 0,
                    'icon' => 'invisible.gif',
                ], [
                    'value' => 1,
                    'icon' => 'visible.gif',
                ]]],
            ],
            'label' => [
                'attributes' => 'onclick="Backend.getScrollOffset();"',
                'haste_ajax_operation' => ['field' => 'label', 'options' => [[
                    'value' => '',
                    'icon' => 'bundles/ferienpasscore/img/gray.svg',
                ], [
                    'value' => 'amber',
                    'icon' => 'bundles/ferienpasscore/img/amber.svg',
                ], [
                    'value' => 'pink',
                    'icon' => 'bundles/ferienpasscore/img/pink.svg',
                ], [
                    'value' => 'purple',
                    'icon' => 'bundles/ferienpasscore/img/purple.svg',
                ], [
                    'value' => 'green',
                    'icon' => 'bundles/ferienpasscore/img/green.svg',
                ]]],
            ],
            'attendances' => [
                'route' => 'backend_offer_applications',
                'icon' => 'bundles/ferienpasscore/img/attendances.svg',
            ],
            'proof' => [
                'route' => 'backend_offer_pdf_proof',
                'icon' => 'assets/contao/images/pdf.svg',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['varbase'],
        'default' => '{admin_legend},varbase,edition,hosts,members,category;{name_legend},name,alias;{text_legend},description,teaser,meetingPoint,bring;{date_legend},dates,datesExport,applicationDeadline;{info_legend},minAge,maxAge,minParticipants,maxParticipants,fee,wheelchairAccessible;{media_legend},image,downloads;{applications_legend},requiresApplication,onlineApplication,applyText,contact,comment;{status_legend},published,cancelled',
    ],
    'fields' => [
        'edition' => [
            'exclude' => true,
            'filter' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'select',
            'foreignKey' => 'Edition.name',
            'relation' => [
                'type' => 'hasOne',
                'load' => 'eager',
            ],
            'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50'],
        ],
        'varbase' => [
            'exclude' => true,
            'inputType' => 'select',
            'foreignKey' => 'Offer.name',
            'relation' => [
                'type' => 'belongsTo',
                'load' => 'eager',
            ],
            'eval' => ['includeBlankOption' => true, 'submitOnChange' => true, 'chosen' => true, 'tl_class' => 'w50'],
            'save_callback' => [fn ($v) => $v ? (int) $v : null],
        ],
        'hosts' => [
            'exclude' => true,
            'filter' => true,
            'flag' => 1,
            'sorting' => true,
            'inputType' => 'select',
            'foreignKey' => 'Host.name',
            'eval' => ['includeBlankOption' => true, 'chosen' => true, 'mandatory' => true, 'multiple' => true, 'tl_class' => 'clr w50'],
            'relation' => [
                'type' => 'haste-ManyToMany',
                'load' => 'lazy',
                'table' => 'Host',
                'field' => 'id',
                'referenceColumn' => 'offer_id',
                'fieldColumn' => 'host_id',
                'relationTable' => 'HostOfferAssociation',
                'skipInstall' => true,
            ],
        ],
        'category' => [
            'exclude' => true,
            'filter' => true,
            'flag' => 1,
            'inputType' => 'select',
            'foreignKey' => 'OfferCategory.name',
            'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'clr w50'],
            'relation' => [
                'type' => 'haste-ManyToMany',
                'load' => 'lazy',
                'table' => 'OfferCategory',
                'field' => 'id',
                'referenceColumn' => 'offer_id',
                'fieldColumn' => 'category_id',
                'relationTable' => 'OfferCategoryAssociation',
                'skipInstall' => true,
            ],
        ],
        'members' => [
            'exclude' => true,
            'inputType' => 'select',
            'filter' => true,
            'foreignKey' => "tl_member.CONCAT(firstname, ' ', lastname)",
            'eval' => ['includeBlankOption' => false, 'chosen' => true, 'mandatory' => false, 'multiple' => true, 'tl_class' => 'w50'],
            'relation' => [
                'type' => 'haste-ManyToMany',
                'load' => 'lazy',
                'table' => 'tl_member',
                'field' => 'id',
                'referenceColumn' => 'offer_id',
                'fieldColumn' => 'member_id',
                'relationTable' => 'OfferMemberAssociation',
                'skipInstall' => true,
            ],
        ],
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
            'eval' => ['readonly' => true, 'unique' => true, 'tl_class' => 'w50', 'doNotCopy' => true],
        ],
        'label' => [
            'filter' => true,
        ],
        'description' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'decodeEntities' => true],
        ],
        'teaser' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'decodeEntities' => true],
        ],
        'dates' => [
            'exclude' => true,
            'sorting' => true,
            'inputType' => 'group',
            'storage' => 'entity',
            'palette' => ['begin', 'end'],
            'fields' => [
                'begin' => [
                    'label' => &$GLOBALS['TL_LANG']['MSC']['offer_date']['start'],
                    'inputType' => 'text',
                    'eval' => ['rgxp' => 'datim', 'tl_class' => 'w50', 'datepicker' => true],
                    'load_callback' => [fn (?\DateTimeInterface $v) => $v ? $v->getTimestamp() : null],
                    'save_callback' => [fn ($v) => $v ? new DateTime(date('Y-m-d H:i', $v)) : null],
                ],
                'end' => [
                    'label' => &$GLOBALS['TL_LANG']['MSC']['offer_date']['end'],
                    'inputType' => 'text',
                    'eval' => ['rgxp' => 'datim', 'tl_class' => 'w50', 'datepicker' => true],
                    'load_callback' => [fn (?\DateTimeInterface $v) => $v ? $v->getTimestamp() : null],
                    'save_callback' => [fn ($v) => $v ? new DateTime(date('Y-m-d H:i', $v)) : null],
                ],
            ],
            'order' => false,
            'eval' => ['tl_class' => 'clr'],
            'relation' => ['type' => 'hasMany', 'table' => 'OfferDate'],
        ],
        'datesExport' => [
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'decodeEntities' => true],
        ],
        'applicationDeadline' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'rgxp' => 'date', 'datepicker' => true],
            'load_callback' => [fn ($v) => $v ? strtotime($v) : null],
            'save_callback' => [fn ($v) => $v ? date('Y-m-d', $v) : null],
        ],
        'minAge' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'rgxp' => 'natural'],
            'save_callback' => [fn ($val) => (int) $val ?: null],
        ],
        'maxAge' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'rgxp' => 'natural'],
            'save_callback' => [fn ($val) => (int) $val ?: null],
        ],
        'meetingPoint' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long'],
        ],
        'bring' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'long'],
        ],
        'wheelchairAccessible' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'radio',
            'options' => [
                '1' => 'Ja',
                '0' => 'Nein',
                '2' => 'Bitte erfragen',
            ],
            'eval' => ['tl_class' => 'clr'],
            'load_callback' => [fn ($v) => null === $v ? '2' : $v],
            'save_callback' => [fn ($v) => \strlen($v) < 2 ? (int) $v : null],
        ],
        'minParticipants' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'save_callback' => [fn ($val) => (int) $val],
        ],
        'maxParticipants' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'save_callback' => [fn ($val) => (int) $val],
        ],
        'fee' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'load_callback' => [fn ($val) => $val / 100 ?: null],
            'save_callback' => [fn ($val) => (int) ((float) $val * 100) ?: null],
        ],
        'image' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'jpg,jpeg,png,gif', 'tl_class' => 'clr'],
            'relation' => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_files', 'field' => 'uuid'],
        ],
        'downloads' => [
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'checkbox', 'multiple' => true, 'files' => true, 'filesOnly' => true, 'extensions' => 'pdf', 'tl_class' => 'clr'],
        ],
        'requiresApplication' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'save_callback' => [fn ($val) => (int) $val],
        ],
        'onlineApplication' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'save_callback' => [fn ($val) => (int) $val],
        ],
        'applyText' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
        ],
        'contact' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
        ],
        'comment' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
        ],
        'published' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'save_callback' => [fn ($val) => (int) $val],
        ],
        'cancelled' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50'],
            'save_callback' => [fn ($val) => (int) $val],
        ],
    ],
];
