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

$GLOBALS['TL_DCA']['Attendance'] = [
    'config' => [
        'dataContainer' => 'Table',
        'closed' => true,
    ],
    'list' => [
        'sorting' => [
            'mode' => 2,
            'fields' => ['createdAt'],
        ],
        'label' => [
            'fields' => ['offer', 'status', 'createdAt'],
            'showColumns' => true,
        ],
        'operations' => [
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
            'attendances' => [
                'route' => 'backend_offer_applications',
                'icon' => 'bundles/ferienpasscore/img/attendances.svg',
            ],
        ],
    ],
    'fields' => [
        'offer' => [
        ],
        'status' => [
        ],
        'createdAt' => [
        ],
    ],
];
