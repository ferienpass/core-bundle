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

/** @noinspection PhpUndefinedMethodInspection */
$table = UserModel::getTable();

/*
 * Fields
 */
$GLOBALS['TL_LANG'][$table]['offer_date_picker'][0] = 'Date-Picker für Angebote';
$GLOBALS['TL_LANG'][$table]['offer_date_picker'][1] = 'Das Date-Period-Picker-Widget für die Ferienpass-Angebote verwenden.';
