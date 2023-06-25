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

use Contao\ArrayUtil;

unset(
    $GLOBALS['BE_MOD']['design']['tpl_editor'],
    $GLOBALS['BE_MOD']['system']['maintenance'],
    $GLOBALS['BE_MOD']['system']['settings'],
);

$GLOBALS['BE_MOD']['content']['offers'] = ['tables' => ['Offer']];
$GLOBALS['BE_MOD']['accounts']['member_parents'] = ['tables' => ['tl_member']];
$GLOBALS['BE_MOD']['accounts']['member_hosts'] = ['tables' => ['tl_member']];
$GLOBALS['BE_MOD']['accounts']['hosts'] = ['tables' => ['Host']];
$GLOBALS['BE_MOD']['accounts']['participants'] = [
    'tables' => ['Participant', 'Attendance'],
];

ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['ferienpass'], 0, [
    'editions' => [
        'tables' => ['Edition', 'EditionTask'],
    ],
]);
