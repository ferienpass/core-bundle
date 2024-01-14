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

namespace Ferienpass\CoreBundle\EventListener\Notification;

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;

trait GetNotificationTokensTrait
{
    private static function getNotificationTokens(Participant $participant, Offer $offering): array
    {
        $tokens = [];

        // Add all offer fields
        $tokens['offer_name'] = $offering->getName();

        // Add all the participant fields
        $tokens['participant_firstname'] = $participant->getFirstname();
        $tokens['participant_email'] = $participant->getEmail();

        // Add all the parent's member fields
        if ($member = $participant->getUser()) {
            $tokens['member_firstname'] = $member->firstname;
            $tokens['member_lastname'] = $member->lastname;
            $tokens['member_email'] = $member->email;
        }

        // Add the participant's email
        $tokens['participant_email'] = $tokens['participant_email'] ?: $tokens['member_email'];

        // Add the host's email
        if ($host = $offering->getHosts()->first()) {
            $tokens['host_email'] = $host->getEmail();
        }

        // Add the admin email
        $tokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        return $tokens;
    }
}
