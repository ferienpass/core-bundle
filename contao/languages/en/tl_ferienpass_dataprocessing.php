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
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_view'][0] = 'Render setting';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_view'][1] = 'Wählen Sie die MetaModels-Render-Einstellung, die für das Generieren der XML-Dateien benutzt werden soll.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem'][0] = 'Filesystem';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem'][1] = 'Wählen Sie das Dateisystem aus.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['export_file_name'][0] = 'Export file name';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['export_file_name'][1] = 'Wählen Sie den Namen der exportierten Datei aus.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['dropbox_access_token'][0] = 'Dropbox access token';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['dropbox_access_token'][1] = 'Bitte lassen Sie sich einen Dropbox-Zugangscode generieren.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['path_prefix'][0] = 'Path prefix';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['path_prefix'][1] = 'Hier können Sie die Dateien in einen Unterordner exportieren lassen.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['sync'][0] = 'Synchronization';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['sync'][1] = 'Wählen Sie aus, ob die Dateien synchronisert werden sollen. Wenn Sie Dateien in der Dropbox löschen, werden sie auch im Websystem gelöscht!';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['xml_single_file'][0] = 'Single file for all items';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['xml_single_file'][1] = 'Select, whether you want all items in one single file exported or one file per item.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['combine_variants'][0] = 'Combine variants';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['combine_variants'][1] = 'Select, if you want a the variants with the variant base combined.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filtering'][0] = 'Apply filter';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filtering'][1] = 'Wenden Sie einen Filter auf die Elemente an.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filterparams'][0] = 'Filter parameters';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_filterparams'][1] = 'Setzen Sie die Parameter der statischen Filter.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby'][0] = 'Sorting';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby'][1] = 'Bitte wählen Sie eine Reihenfolge für die Sortierung aus.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby_direction'][0] = 'Sorting direction';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_sortby_direction'][1] = 'In aufsteigender oder absteigender Reihenfolge';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_limit'][0] = 'Limit items';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_limit'][1] = 'Bitte geben Sie maximale Anzahl der Datensätze an. Geben Sie 0 an, um alle Datensätze anzuzeigen.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_offset'][0] = 'Offset for items';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_offset'][1] = 'Bitte geben Sie den Wert für den Offset an (beispielsweise 10, um die ersten 10 Items zu überspringen).';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['static_dirs'][0] = 'Additional folders';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['static_dirs'][1] = 'Wählen Sie Ordner aus, die zusätzlich exportiert werden sollen.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['variant_delimiters'][0] = 'Variant delimiter';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['variant_delimiters'][1] = 'Choose the characters used to separate two variant values.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_attribute'][0] = 'Attribute';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['metamodel_attribute'][1] = 'Use the delimiter for this attribute. Keep empty to use the delimiter for all attributes or as fallback.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['delimiter'][0] = 'Delimiter';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline'][0] = 'Newline';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline'][1] = 'Insert a newline before/after the delimiter.';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline_position'][0] = 'Newline position';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline_position'][1] = 'Select where to place the newline.';

/*
 * References
 */
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline_positions']['before'] = 'Before delimiter';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['newline_positions']['after'] = 'After delimiter';

/*
 * Options
 */
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem_options']['local'] = 'Save files';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem_options']['sendToBrowser'] = 'Download files';
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
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['format_legend'] = 'Format';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['filesystem_legend'] = 'Filesystem';
$GLOBALS['TL_LANG']['tl_ferienpass_dataprocessing']['scope_legend'] = 'Export scope';
