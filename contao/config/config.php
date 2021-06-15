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

use Ferienpass\CoreBundle\Backend\PassEditionStatistics;

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

array_insert($GLOBALS['BE_MOD']['ferienpass'], 0, [
    'editions' => [
        'tables' => ['Edition', 'EditionTask'],
        'stats' => [PassEditionStatistics::class, 'execute'],
    ],
]);

$GLOBALS['NOTIFICATION_CENTER']['GATEWAY']['email'] = \Ferienpass\CoreBundle\NotificationCenter\Gateway\Email::class;

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'] = array_merge((array) $GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE'], [
    'ferienpass' => [
        'attendance_changed_confirmed' => [
            'recipients' => [
                'participant_email',
                'host_email',
                'admin_email',
                'member_email',
                'recipient_email',
            ],
            'sms_recipients' => [
                'member_mobile',
                'member_phone',
                'participant_mobile',
                'participant_phone',
            ],
            'email_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_html' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_sender_name' => [
                'admin_email',
            ],
            'email_sender_address' => [
                'admin_email',
            ],
            'email_recipient_cc' => [
                'admin_email',
            ],
            'email_recipient_bcc' => [
                'admin_email',
            ],
            'email_replyTo' => [
                'admin_email',
            ],
            'sms_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'sms_recipients_region' => [
                'participant_country',
                'member_country',
            ],
        ],
        'attendance_changed_withdrawn' => [
            'recipients' => [
                'participant_email',
                'host_email',
                'admin_email',
                'member_email',
                'recipient_email',
            ],
            'sms_recipients' => [
                'member_mobile',
                'member_phone',
                'participant_mobile',
                'participant_phone',
            ],
            'email_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_html' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_sender_name' => [
                'admin_email',
            ],
            'email_sender_address' => [
                'admin_email',
            ],
            'email_recipient_cc' => [
                'admin_email',
            ],
            'email_recipient_bcc' => [
                'admin_email',
            ],
            'email_replyTo' => [
                'admin_email',
            ],
            'sms_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'sms_recipients_region' => [
                'participant_country',
                'member_country',
            ],
        ],
        'attendance_created_confirmed' => [
            'recipients' => [
                'participant_email',
                'host_email',
                'admin_email',
                'member_email',
            ],
            'sms_recipients' => [
                'member_mobile',
                'member_phone',
                'participant_mobile',
                'participant_phone',
            ],
            'email_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_html' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_sender_name' => [
                'admin_email',
            ],
            'email_sender_address' => [
                'admin_email',
            ],
            'email_recipient_cc' => [
                'admin_email',
            ],
            'email_recipient_bcc' => [
                'admin_email',
            ],
            'email_replyTo' => [
                'admin_email',
            ],
            'sms_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'sms_recipients_region' => [
                'participant_country',
                'member_country',
            ],
        ],
        'offer_cancelled' => [
            'recipients' => [
                'participant_email',
                'host_email',
                'admin_email',
                'member_email',
            ],
            'sms_recipients' => [
                'member_mobile',
                'member_phone',
                'participant_mobile',
                'participant_phone',
            ],
            'email_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_html' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_sender_name' => [
                'admin_email',
            ],
            'email_sender_address' => [
                'admin_email',
            ],
            'email_recipient_cc' => [
                'admin_email',
            ],
            'email_recipient_bcc' => [
                'admin_email',
            ],
            'email_replyTo' => [
                'admin_email',
            ],
            'sms_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'sms_recipients_region' => [
                'participant_country',
                'member_country',
            ],
        ],
        'offer_relaunched' => [
            'recipients' => [
                'participant_email',
                'host_email',
                'admin_email',
                'member_email',
            ],
            'sms_recipients' => [
                'member_mobile',
                'member_phone',
                'participant_mobile',
                'participant_phone',
            ],
            'email_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_html' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_sender_name' => [
                'admin_email',
            ],
            'email_sender_address' => [
                'admin_email',
            ],
            'email_recipient_cc' => [
                'admin_email',
            ],
            'email_recipient_bcc' => [
                'admin_email',
            ],
            'email_replyTo' => [
                'admin_email',
            ],
            'sms_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'sms_recipients_region' => [
                'participant_country',
                'member_country',
            ],
        ],
        'application_list_reminder' => [
            'recipients' => [
                'participant_email',
                'host_email',
                'admin_email',
                'member_email',
            ],
            'sms_recipients' => [
                'member_mobile',
                'member_phone',
                'participant_mobile',
                'participant_phone',
            ],
            'email_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_html' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'email_sender_name' => [
                'admin_email',
            ],
            'email_sender_address' => [
                'admin_email',
            ],
            'email_recipient_cc' => [
                'admin_email',
            ],
            'email_recipient_bcc' => [
                'admin_email',
            ],
            'email_replyTo' => [
                'admin_email',
            ],
            'sms_text' => [
                'offer_*',
                'participant_*',
                'member_*',
            ],
            'sms_recipients_region' => [
                'participant_country',
                'member_country',
            ],
        ],
        'admission_letter' => [
            'recipients' => [
                'admin_email',
                'recipient_email',
            ],
            'email_text' => [
                'recipient_firstname',
                'recipient_lastname',
                'recipient_email',
                'admissions',
                'link',
            ],
        ],
        'pdf_proofs' => [
            'recipients' => [
                'recipient_email',
            ],
            'email_text' => [
                'recipient_firstname',
                'recipient_lastname',
                'recipient_email',
            ],
            'attachment_tokens' => [
                'attachment',
            ],
        ],
        'host_invite_member' => [
            'recipients' => [
                'invitee_email',
            ],
            'email_text' => [
                'link',
                'host_*',
                'member_*',
            ],
            'email_html' => [
                'link',
                'host_*',
                'member_*',
            ],
            'email_sender_name' => [
                'admin_email',
                'member_firstname',
                'member_lastname',
            ],
            'email_sender_address' => [
                'admin_email',
                'member_email',
            ],
            'email_recipient_cc' => [
                'admin_email',
            ],
            'email_recipient_bcc' => [
                'admin_email',
            ],
            'email_replyTo' => [
                'admin_email',
                'member_email',
            ],
        ],
        'unacknowledged_attendances' => [
            'recipients' => [
                'participant_email',
                'admin_email',
                'member_email',
            ],
            'sms_recipients' => [
                'participant_mobile',
                'member_mobile',
            ],
            'email_text' => [
                'participant_*',
                'member_*',
            ],
            'email_html' => [
                'participant_*',
                'member_*',
            ],
            'email_sender_name' => [
                'admin_email',
            ],
            'email_sender_address' => [
                'admin_email',
            ],
            'email_recipient_cc' => [
                'admin_email',
            ],
            'email_recipient_bcc' => [
                'admin_email',
            ],
            'email_replyTo' => [
                'admin_email',
            ],
            'sms_text' => [
                'participant_*',
                'member_*',
            ],
            'sms_recipients_region' => [
                'member_country',
            ],
        ],
    ],
]);
