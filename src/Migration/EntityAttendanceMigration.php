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

class EntityAttendanceMigration extends AbstractMigration
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Migrate to Attendance entity';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (null === $schemaManager || !$schemaManager->tablesExist(['tl_ferienpass_attendance', 'Attendance', 'Offer', 'EditionTask'])) {
            return false;
        }

        return $this->connection->query('SELECT id FROM Offer')->rowCount() > 0
               && $this->connection->query('SELECT id FROM tl_ferienpass_attendance')->rowCount() > 0
               && 0 === $this->connection->query('SELECT id FROM Attendance')->rowCount();
    }

    public function run(): MigrationResult
    {
        $this->connection->query('
INSERT INTO Attendance(id, tstamp, sorting, createdAt, status, offer_id, participant_id, task_id)
SELECT a.id, a.tstamp, a.sorting, FROM_UNIXTIME(a.created), a.status, o.id, p.id, t.id
FROM tl_ferienpass_attendance a
LEFT JOIN Participant p on p.id=a.participant
INNER JOIN Offer o on o.id=a.offer
LEFT JOIN EditionTask t on t.id=a.task
'
        );

        return $this->createResult(true);
    }
}
