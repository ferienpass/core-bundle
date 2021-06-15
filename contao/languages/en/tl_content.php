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

// For the task options
\Contao\Controller::loadLanguageFile('EditionTask');

$GLOBALS['TL_LANG']['tl_content']['ferienpass_task_condition'][0] = 'Only show in';
$GLOBALS['TL_LANG']['tl_content']['ferienpass_task_condition'][1] = 'Only show the content element if this task is active.';
$GLOBALS['TL_LANG']['tl_content']['ferienpass_task_condition_inverted'][0] = '…if not active';
$GLOBALS['TL_LANG']['tl_content']['ferienpass_task_condition_inverted'][1] = 'Do NOT show the content element if the selected task is active.';
