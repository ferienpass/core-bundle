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
use Doctrine\DBAL\Exception\InvalidFieldNameException;

class RoutingUrlSuffixMigration extends AbstractMigration
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Update to the new routing in Contao 4.10';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (null === $schemaManager || !$schemaManager->tablesExist(['tl_page'])) {
            return false;
        }

        try {
            return $this->connection->query("SELECT id FROM tl_page WHERE urlSuffix='.html';")->rowCount() > 0;
        } catch (InvalidFieldNameException $e) {
            return false;
        }
    }

    public function run(): MigrationResult
    {
        $this->connection->query("UPDATE tl_page SET urlSuffix='' WHERE urlSuffix='.html'");

        return $this->createResult(true);
    }
}
