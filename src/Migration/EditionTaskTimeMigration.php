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

class EditionTaskTimeMigration extends AbstractMigration
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Migrate Edition and EditonTask tables';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (null === $schemaManager || !$schemaManager->tablesExist(['tl_ferienpass_edition', 'tl_ferienpass_edition_task', 'Edition', 'EditionTask'])) {
            return false;
        }

        return $this->connection->query('SELECT id FROM tl_ferienpass_edition')->rowCount() > 0
               && 0 === $this->connection->query('SELECT id FROM Edition')->rowCount();
    }

    public function run(): MigrationResult
    {
        $this->connection->query('
INSERT INTO Edition(id, tstamp, `name`, alias)
SELECT id, tstamp, `title`, alias
FROM tl_ferienpass_edition
'
        );

        $this->connection->query('
INSERT INTO EditionTask(id, pid, tstamp, sorting, `type`, title, application_system, color, description, max_applications, hide_status, age_check, max_applications_day, periodBegin, periodEnd)
SELECT t.id, e.id, t.tstamp, t.sorting, t.`type`, t.title, t.application_system, color, description, max_applications, hide_status, age_check, max_applications_day, FROM_UNIXTIME(period_start), FROM_UNIXTIME(period_stop)
FROM tl_ferienpass_edition_task t
INNER JOIN Edition e on e.id=t.pid
'
        );

        return $this->createResult(true);
    }
}
