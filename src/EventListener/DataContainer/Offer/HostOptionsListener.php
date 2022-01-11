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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Offer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Ferienpass\CoreBundle\Entity\Offer;

class HostOptionsListener
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @Callback(table="Offer", target="fields.hosts.options")
     */
    public function optionsCallback(DataContainer $dc = null): array
    {
        $return = [];

        $qb = $this->connection->createQueryBuilder()
            ->select('id', 'name')
            ->from('Host', 'h')
            ->orderBy('name');

        if ($dc && !$dc->id) {
            $qb->innerJoin('h', 'HostOfferAssociation', 'a', 'a.host_id=h.id');
        }

        foreach ($qb->executeQuery()->iterateAssociative() as $item) {
            $return[$item['id']] = $item['name'];
        }

        return $return;
    }
}
