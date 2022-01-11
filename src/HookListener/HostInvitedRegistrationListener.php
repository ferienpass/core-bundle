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

namespace Ferienpass\CoreBundle\HookListener;

use Contao\Input;
use Contao\MemberModel;
use Doctrine\DBAL\Connection;

class HostInvitedRegistrationListener
{
    public function __construct(private Connection $connection)
    {
    }

    public function onCreateNewUser($newUserId): void
    {
        $inviteToken = Input::get('invite');
        if (null === $inviteToken) {
            return;
        }

        // Fetch host from invite token.
        $statement = $this->connection->createQueryBuilder()
            ->select('host')
            ->from('tl_ferienpass_host_invite_token')
            ->where('token=:token')
            ->andWhere('expires>:time')
            ->setParameter('token', $inviteToken)
            ->setParameter('time', time())
            ->executeQuery();

        if (false === $statement) {
            return;
        }

        $memberModel = MemberModel::findByPk($newUserId);
        if (null === $memberModel) {
            throw new \RuntimeException('Member not found: ID'.$newUserId);
        }

        // Assign host.
        $memberModel->ferienpass_host = $statement->fetchOne();
        $memberModel->save();

        // Invalidate token.
        $this->connection->createQueryBuilder()
            ->delete('tl_ferienpass_host_invite_token')
            ->where('token=:token')
            ->setParameter('token', $inviteToken);
    }
}
