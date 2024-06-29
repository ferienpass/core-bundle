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

class PaymentEntityMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist('PaymentItem')) {
            return false;
        }

        if (!$schemaManager->tablesExist('PaymentItemAssociation')) {
            return false;
        }

        if (\in_array('payment_id', array_keys($schemaManager->listTableColumns('PaymentItem')), true)) {
            return false;
        }

        return true;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement(' ALTER TABLE PaymentItem ADD payment_id INT UNSIGNED DEFAULT NULL');

        $items = $this->connection->fetchAllAssociative('SELECT payment_id, item_id FROM PaymentItemAssociation');

        foreach ($items as $kv) {
            $this->connection->executeStatement("UPDATE PaymentItem SET payment_id={$kv['payment_id']} WHERE id={$kv['item_id']}");
        }

        return new MigrationResult(true, 'OK');
    }
}
