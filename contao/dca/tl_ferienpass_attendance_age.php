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

$GLOBALS['TL_DCA']['tl_ferienpass_attendance_age'] = [
    'config' => [
        'sql' => [
            'keys' => [
                'attendance' => 'primary',
            ],
        ],
    ],
    'fields' => [
        'attendance' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'age' => [
            'sql' => 'int(3) unsigned NULL',
        ],
        'participant_id' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];
