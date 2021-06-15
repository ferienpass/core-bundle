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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Participant;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\MemberModel;

class LabelListener
{
    /**
     * @Callback(table="Participant", target="list.label.label")
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $labels)
    {
        $member = MemberModel::findByPk($row['member_id']);

        $labels[0] = sprintf('%s %s', $row['firstname'], $row['lastname']);

        $labels[2] = trim(sprintf('%s / %s', $row['phone'] ?: ($member ? $member->phone : ''), $row['mobile'] ?: ($member ? $member->mobile : '')), '/ ');
        $labels[3] = $row['email'] ?: ($member ? $member->email : '');

        return $labels;
    }
}
