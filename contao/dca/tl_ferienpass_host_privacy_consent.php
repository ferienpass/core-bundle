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

$GLOBALS['TL_DCA']['tl_ferienpass_host_privacy_consent'] = [
    // Config
    'config' => [
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'member' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'statement_hash' => [
            'sql' => "varchar(40) NOT NULL default ''",
        ],
        'form_data' => [
            'sql' => 'text NULL',
        ],
    ],
];
