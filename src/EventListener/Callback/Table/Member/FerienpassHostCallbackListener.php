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

namespace Ferienpass\CoreBundle\EventListener\Callback\Table\Member;

use Contao\DataContainer;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class FerienpassHostCallbackListener
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * Throw error if no host is set for member in host group.
     */
    public function onSaveCallback($value, DataContainer $dc)
    {
        if ('' === $value && \in_array('1', StringUtil::deserialize($dc->activeRecord->groups, true), true)) {
            throw new \RuntimeException($GLOBALS['TL_LANG']['ERR']['missingHostForMember']);
        }

        return $value;
    }

    /**
     * Add the host member group if host is set.
     *
     * @param DataContainer $dataContainer
     */
    public function onSubmitCallback($dataContainer): void
    {
        if (!$dataContainer instanceof DataContainer) {
            // In frontend, the first argument passed is no data container (see ModulePersonalData).
            return;
        }

        $groups = (array) StringUtil::deserialize($dataContainer->activeRecord->groups, true);
        $host = $dataContainer->activeRecord->ferienpass_host;

        if ($host && !\in_array('1', $groups, true)) {
            $groups[] = '1';
        }

        $dataContainer->activeRecord->groups = $groups;

        $this->connection->createQueryBuilder()
            ->update('tl_member')
            ->set('`groups`', '?')
            ->where('id=?')
            ->setParameter(0, serialize($groups))
            ->setParameter(1, $dataContainer->activeRecord->id)
            ->executeQuery()
        ;
    }
}
