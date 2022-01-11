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

class DatesSortingListener
{
    public function __construct(private Connection $connection)
    {
    }

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
    }
}
