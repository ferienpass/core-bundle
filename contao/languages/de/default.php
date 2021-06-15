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
 * Miscellaneous.
 */
$GLOBALS['TL_LANG']['MSC']['editParticipant'] = 'Teilnehmer bearbeiten';
$GLOBALS['TL_LANG']['MSC']['addNewParticipant'] = 'Einen neuen Teilnehmer erstellen';
$GLOBALS['TL_LANG']['MSC']['noAttendances'] = 'Es liegen keine Anmeldungen vor.';
$GLOBALS['TL_LANG']['MSC']['noParticipants'] = 'Keine Kinder zur Anmeldung gefunden! Sie müssen zuerst Ihre Kinder anlegen unter "Meine Kinder & Daten".';

$GLOBALS['TL_LANG']['MSC']['mm_participant']['noItemsMsg'] = 'Keine Teilnehmer gefunden! Legen Sie jetzt Kinder an, die Sie später zu Angeboten anmelden wollen.';

// User application
$GLOBALS['TL_LANG']['MSC']['user_application']['active'] = 'Dieses Angebot verwendet das Online-Anmeldeverfahren.';
$GLOBALS['TL_LANG']['MSC']['user_application']['no_applications'] = 'Es sind keine Anmeldungen notwendig, um am Angebot teilzunehmen.';
$GLOBALS['TL_LANG']['MSC']['user_application']['inactive'] = 'Bitte melden Sie sich direkt beim Veranstalter, wenn Sie am Angebot teilnehmen wollen.';
$GLOBALS['TL_LANG']['MSC']['user_application']['past'] = 'Dieses Angebot liegt in der Vergangenheit.';
$GLOBALS['TL_LANG']['MSC']['user_application']['cancelled'] = 'Das Angebot findet nicht mehr statt.';
$GLOBALS['TL_LANG']['MSC']['user_application']['participant']['label'] = 'Teilnehmer auswählen';
$GLOBALS['TL_LANG']['MSC']['user_application']['participant']['placeholder'] = 'Hier klicken und Teilnehmer auswählen';
$GLOBALS['TL_LANG']['MSC']['user_application']['participant']['slabel'] = 'Anmelden';
$GLOBALS['TL_LANG']['MSC']['user_application']['message']['confirmed'] = '%s ist angemeldet für dieses Angebot.';
$GLOBALS['TL_LANG']['MSC']['user_application']['message']['waiting'] = '%s ist vorgemerkt und wartet auf die Zuteilung für dieses Angebot.';
$GLOBALS['TL_LANG']['MSC']['user_application']['message']['waiting-list'] = '%s steht auf der Warteliste für dieses Angebot.';
$GLOBALS['TL_LANG']['MSC']['user_application']['message']['error'] = '%s ist für dieses Angebot nicht angemeldet.';
$GLOBALS['TL_LANG']['MSC']['user_application']['error'] = 'Ein Fehler ist aufgetreten.';
$GLOBALS['TL_LANG']['MSC']['user_application']['high_utilization_text'] = 'Es wollen mehr Kinder teilnehmen, als es Plätze gibt. Die aktuelle Auslastung liegt bei %d %%.';
$GLOBALS['TL_LANG']['MSC']['user_application']['variants_list_link'] = 'Alternative Termine zum gleichen Termin';

$GLOBALS['TL_LANG']['MSC']['welcome_gantt']['task_description']['application_system']['lot'] = 'Es läuft das Los-Verfahren. Die Eltern erhalten zunächst keine Zusage, Sie müssen erst alle Anmeldungen zulosen. Wenn kein Anmeldesystem läuft, sind keine An- oder Abmeldungen möglich!';
$GLOBALS['TL_LANG']['MSC']['welcome_gantt']['task_description']['application_system']['firstcome'] = 'Es läuft das Windhund-Anmeldeverfahren. Das bedeutet, dass die Kinder sofort auf die Teilnehmerliste geschrieben werden und sofort im Anschluss eine Zusage erhalten. Sie müssen dann nichts mehr tun. Wenn kein Anmeldesystem läuft, sind keine An- oder Abmeldungen möglich!';
$GLOBALS['TL_LANG']['MSC']['welcome_gantt']['task_description']['holiday'] = 'Es sind Ferien. Diese Zeitangabe wird vor allem verwendet für die Kalender-Widgets.';
$GLOBALS['TL_LANG']['MSC']['welcome_gantt']['task_description']['host_editing_stage'] = 'In der Bearbeitungsphase für Veranstalter können die Veranstalter ihre Angebote erstellen, bearbeiten und löschen.';
$GLOBALS['TL_LANG']['MSC']['welcome_gantt']['task_description']['show_offers'] = 'Die Ferienpass-Angebote werden auf der Webseite angezeigt. Wenn nicht aktiv, erscheint eine leere Ausgabe im Frontend.';
$GLOBALS['TL_LANG']['MSC']['welcome_gantt']['task_description']['pay_days'] = 'Es sind Zahltage. Die Eltern können auf der Webseite die offenen Teilnahmegebühren online bezahlen.';
$GLOBALS['TL_LANG']['MSC']['welcome_gantt']['task_description']['allocation'] = 'Sie sind an der Reihe und müssen die Kinder auf die verfügbaren Plätze verteilen.';
$GLOBALS['TL_LANG']['MSC']['welcome_gantt']['task_description']['publish_lists'] = 'Die Veranstalter können die Teilnahmelisten einsehen.';

$GLOBALS['TL_LANG']['MSC']['application_list']['inactive'] = 'Dieses Angebot verwendet nicht das Online-Anmeldesystem.';
$GLOBALS['TL_LANG']['MSC']['application_list']['privacy_statement_missing'] = 'Sie müssen vorher die Datenschutzerklärung unterzeichnen.';

$GLOBALS['TL_LANG']['MSC']['attendance_status']['confirmed'] = 'Zusage';
$GLOBALS['TL_LANG']['MSC']['attendance_status']['waiting'] = 'wartend';
$GLOBALS['TL_LANG']['MSC']['attendance_status']['waitlisted'] = 'Warteliste';
$GLOBALS['TL_LANG']['MSC']['attendance_status']['error'] = 'abgelehnt';
$GLOBALS['TL_LANG']['MSC']['attendance_status']['withdrawn'] = 'abgemeldet';
$GLOBALS['TL_LANG']['MSC']['attendance_status']['cancelled'] = 'Angebot abgesagt';

$GLOBALS['TL_LANG']['MSC']['application_system']['firstcome'] = 'Windhund-Verfahren';
$GLOBALS['TL_LANG']['MSC']['application_system']['lot'] = 'Los-Verfahren';

// Add attendee as host
$GLOBALS['TL_LANG']['MSC']['addAttendeeHost']['submit'] = 'Teilnehmer verbindlich hinzufügen';
$GLOBALS['TL_LANG']['MSC']['addAttendeeHost']['confirmation'] = 'Es wurden %u Teilnehmer zu diesem Angebot hinzugefügt.';
$GLOBALS['TL_LANG']['MSC']['document']['export_error'] = 'Ein Fehler beim Export ist aufgetreten';

$GLOBALS['TL_LANG']['MSC']['downloadList'][0] = 'Teilnehmerliste downloaden';
$GLOBALS['TL_LANG']['MSC']['downloadList'][1] = 'Die Teilnehmerliste zu diesem Angebot als PDF herunterladen';

$GLOBALS['TL_LANG']['MSC']['itemConfirmDeleteLink'] = 'Wollen Sie das Angebot %s wirklich löschen?';
$GLOBALS['TL_LANG']['MSC']['itemDeleteConfirmation'] = 'Das Angebot wurde erfolgreicht gelöscht.';

$GLOBALS['TL_LANG']['MSC']['state'] = 'Status';
$GLOBALS['TL_LANG']['MSC']['recall'] = 'Zurückziehen';

$GLOBALS['TL_LANG']['MSC']['yesno'][0] = 'Nein';
$GLOBALS['TL_LANG']['MSC']['yesno'][1] = 'Ja';
$GLOBALS['TL_LANG']['MSC']['offer_date']['start'][0] = 'Beginn';
$GLOBALS['TL_LANG']['MSC']['offer_date']['end'][0] = 'Ende';

$GLOBALS['TL_LANG']['MSC']['ferienpass_code_invalid'] = 'Der eingegebene Code ist ungültig oder bereits verwendet worden.';

/*
 * Errors
 */
$GLOBALS['TL_LANG']['ERR']['ageInputMissingValues'] = 'Bitte füllen Sie alle notwendigen Werte für die Angabe "%s" aus.';
$GLOBALS['TL_LANG']['ERR']['ageInputReverseAgeRanges'] = 'Ihre eingegeben Altersgrenze <em>%s</em> ist nicht höher als die Altersgrenze <em>%s</em>.';
$GLOBALS['TL_LANG']['ERR']['changedDateOfBirthAfterwards'] = 'Das Geburtsdatum kann nicht mehr verändert werden, nachdem Sie Ihr Kind zu Angeboten angemeldet haben.';
$GLOBALS['TL_LANG']['ERR']['changedAgreementPhotosAfterwards'] = 'Die Einverständniserklärung kann nicht mehr widerrufen werden, nachdem Sie Ihr Kind zu Angeboten angemeldet haben.';
$GLOBALS['TL_LANG']['ERR']['missingHostForMember'] = 'Bitte erstellen Sie vorerst einen Veranstalter und ordnen Sie diesen hier zu.';
