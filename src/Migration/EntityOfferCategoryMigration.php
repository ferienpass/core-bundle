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

namespace Ferienpass\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class EntityOfferCategoryMigration extends AbstractMigration
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Migrate to OfferCategory entity';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (null === $schemaManager || !$schemaManager->tablesExist(['mm_ferienpass_category', 'OfferCategory'])) {
            return false;
        }

        return $this->connection->query('SELECT id FROM mm_ferienpass_category')->rowCount() > 0
               && 0 === $this->connection->query('SELECT id FROM OfferCategory')->rowCount();
    }

    public function run(): MigrationResult
    {
        $this->connection->query(
            '
INSERT INTO OfferCategory(id, tstamp, name, alias)
SELECT id, tstamp, title, alias
FROM mm_ferienpass_category
'
        );

        return $this->createResult(true);
    }
}
