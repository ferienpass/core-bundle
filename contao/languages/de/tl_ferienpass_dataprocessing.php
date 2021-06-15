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

/*
 * Fields.
 */
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['name'][0] = 'Name';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['name'][1] = 'Geben Sie einen Namen für die Datenverarbeitung an. Dieser wird nur im Backend benutzt.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['format'][0] = 'Format';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['format'][1] = 'Wählen Sie das Dateiformat aus.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_view'][0] = 'Render-Einstellung';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_view'][1] = 'Wählen Sie die MetaModels-Render-Einstellung, die für das Generieren der XML-Dateien benutzt werden soll.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem'][0] = 'Dateisystem';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem'][1] = 'Wählen Sie das Dateisystem aus.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['export_file_name'][0] = 'Export-Dateiname';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['export_file_name'][1] = 'Wählen Sie den Namen der exportierten Datei aus.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['dropbox_access_token'][0] = 'Dropbox-Access-Token';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['dropbox_access_token'][1] = 'Bitte lassen Sie sich einen Dropbox-Zugangscode generieren.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['path_prefix'][0] = 'Pfad-Prefix';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['path_prefix'][1] = 'Hier können Sie die Dateien in einen Unterordner exportieren lassen.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['sync'][0] = 'Synchronisation';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['sync'][1] = 'Wählen Sie aus, ob die Dateien synchronisert werden sollen. Wenn Sie Dateien in der Dropbox löschen, werden sie auch im Websystem gelöscht!';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['xml_single_file'][0] = 'Eine Datei für alle Elemente';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['xml_single_file'][1] = 'Wählen Sie, ob sie alle Elemente in einer Datei exportiert brauchen, oder für jedes Element eine einzelne Datei.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['combine_variants'][0] = 'Varianten kombinieren';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['combine_variants'][1] = 'Wählen Sie, ob Sie die Varianten im gleichen Element exportiert haben möchten.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filtering'][0] = 'Fiter anwenden';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filtering'][1] = 'Wenden Sie einen Filter auf die Elemente an.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filterparams'][0] = 'Fiterparameter';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filterparams'][1] = 'Setzen Sie die Parameter der statischen Filter.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby'][0] = 'Sortieren nach';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby'][1] = 'Bitte wählen Sie eine Reihenfolge für die Sortierung aus.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby_direction'][0] = 'Sortierreihenfolge';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby_direction'][1] = 'In aufsteigender oder absteigender Reihenfolge';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_limit'][0] = 'Maximale Anzahl der Datensätze die angezeigt werden sollen.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_limit'][1] = 'Bitte geben Sie maximale Anzahl der Datensätze an. Geben Sie 0 an, um alle Datensätze anzuzeigen.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_offset'][0] = 'Listen-Offset';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_offset'][1] = 'Bitte geben Sie den Wert für den Offset an (beispielsweise 10, um die ersten 10 Items zu überspringen).';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['static_dirs'][0] = 'Zusätzliche Ordner';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['static_dirs'][1] = 'Wählen Sie Ordner aus, die zusätzlich exportiert werden sollen.';

/*
 * Options
 */
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem_options']['sendToBrowser'] =
    'Dateien komprimiert herunterladen';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem_options']['dropbox'] = 'Dropbox';

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['new'][0] = 'Neue Datenverarbeitung';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['new'][1] = 'Neue Datenverarbeitung erstellen';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['edit'][0] = 'Datenverarbeitung bearbeiten';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['edit'][1] = 'Datenverarbeitung ID %s bearbeiten';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['copy'][0] = 'Datenverarbeitung kopieren';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['copy'][1] = 'Datenverarbeitung ID %s kopieren';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['delete'][0] = 'Datenverarbeitung löschen';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['delete'][1] = 'Datenverarbeitung ID %s löschen';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['show'][0] = 'Datenverarbeitungsdetails';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['show'][1] = 'Details von Datenverarbeitung ID %s anzeigen';

/*
 * Legends
 */
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['title_legend'] = 'Name';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['format_legend'] = 'Dateiformat';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem_legend'] = 'Dateisystem';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['scope_legend'] = 'Export-Bereich';
