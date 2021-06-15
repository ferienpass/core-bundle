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

class EntityParticipantMigration extends AbstractMigration
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Migrate to Particpiant entity';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (null === $schemaManager || !$schemaManager->tablesExist(['tl_member', 'mm_participant', 'Participant'])) {
            return false;
        }

        return $this->connection->query('SELECT id FROM mm_participant')->rowCount() > 0
               && 0 === $this->connection->query('SELECT id FROM Participant')->rowCount();
    }

    public function run(): MigrationResult
    {
        $this->connection->query(
            '
INSERT INTO Participant(id, tstamp, firstname, lastname, dateOfBirth, phone, email, member_id)
SELECT p.id, p.tstamp, p.firstname, p.lastname, FROM_UNIXTIME(p.dateOfBirth), p.phone, p.email, m.id
FROM mm_participant p
LEFT JOIN tl_member m ON m.id=p.pmember
'
        );

        return $this->createResult(true);
    }
}
