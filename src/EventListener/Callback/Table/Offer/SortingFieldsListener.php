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

namespace Ferienpass\CoreBundle\EventListener\Callback\Table\Offer;

use Contao\DataContainer;
use Doctrine\DBAL\Connection;

class SortingFieldsListener
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @var DataContainer|mixed
     */
    public function __invoke($dataContainer): void
    {
        if (!$dataContainer instanceof DataContainer) {
            return;
        }

        $this->connection->executeQuery(<<<'SQL'
UPDATE Offer O
INNER JOIN OfferDate OD on O.id = OD.offer_id
SET O.dates = UNIX_TIMESTAMP(OD.begin)
WHERE OD.begin IS NOT NULL
AND O.id = :id
SQL
            , ['id' => $dataContainer->activeRecord->id]);

        $this->connection->executeQuery(<<<'SQL'
UPDATE Offer O
INNER JOIN HostOfferAssociation HOA on HOA.offer_id = O.id
INNER JOIN Host H on HOA.host_id = H.id
SET O.hosts = H.name
WHERE O.id = :id
SQL
            , ['id' => $dataContainer->activeRecord->id]);
    }
}
