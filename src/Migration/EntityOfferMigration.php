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
use Doctrine\DBAL\Exception;

class EntityOfferMigration extends AbstractMigration
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Migrate to Offer entity';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (null === $schemaManager || !$schemaManager->tablesExist(['mm_ferienpass', 'Offer', 'Edition'])) {
            return false;
        }

        return $this->connection->query('SELECT id FROM Edition')->rowCount() > 0
               && $this->connection->query('SELECT id FROM mm_ferienpass')->rowCount() > 0
               && 0 === $this->connection->query('SELECT id FROM Offer')->rowCount();
    }

    public function run(): MigrationResult
    {
        $bring = $this->bringExists() ? ', bring' : '';
        $comment = $this->commentExists() ? ', comment' : '';
        $applySelect = $this->applicationNotesExist() ? ', application_notes' : '';
        $applyInsert = $this->applicationNotesExist() ? ', applyText' : '';
        $aktivpassInsert = $this->aktivPassExists() ? ', aktivPass' : '';
        $aktivpassSelect = $this->aktivPassExists() ? ", if(aktivpass='1', 1, 0)" : '';
        $applicationDeadlineInsert = $this->applicationDeadlineExists() ? ', applicationDeadline' : '';
        $applicationDeadlineSelect = $this->applicationDeadlineExists() ? ', if(application_deadline, FROM_UNIXTIME(application_deadline), null)' : '';
        $requires_application = $this->requiresApplicationExists() ? "if(requires_application='1', 1, 0)" : "if(applicationlist_active='1', 1, 0)";

        $this->connection->query("
INSERT INTO Offer(id, varbase, tstamp, edition, `name`, alias, description $applyInsert $bring $comment $aktivpassInsert $applicationDeadlineInsert, image, published, requiresApplication, onlineApplication, minParticipants, maxParticipants, fee, meetingPoint, cancelled, minAge, maxAge)
SELECT f.id, if(f.varbase, null, f.vargroup), f.tstamp, if(edition.id, edition.id, null), f.`name`, f.alias, description $applySelect $bring $comment $aktivpassSelect $applicationDeadlineSelect, image, if(published='1', 1, 0), $requires_application, if(applicationlist_active='1', 1, 0), applicationlist_min, applicationlist_max, fee*100, meeting_point, if(cancelled='1', 1, 0), age.lower, age.upper
FROM mm_ferienpass f
LEFT JOIN Edition AS edition ON f.pass_edition = edition.id
LEFT JOIN tl_metamodel_age AS age ON f.id = age.item_id
"
        );

        $this->connection->query('
INSERT INTO OfferDate(offer_id, `begin`, `end`)
SELECT item_id, FROM_UNIXTIME(`start`), FROM_UNIXTIME(`end`)
FROM tl_metamodel_offer_date
'
        );

        $this->connection->query('
INSERT INTO HostOfferAssociation(offer_id, host_id)
SELECT f.id, h.id
FROM mm_ferienpass f
INNER JOIN mm_host h ON h.id = f.host
'
        );

        if ($this->contactPersonExists()) {
            $this->connection->query('
INSERT INTO OfferMemberAssociation(offer_id, member_id)
SELECT f.id, f.contact_person
FROM mm_ferienpass f
INNER JOIN tl_member m ON m.id=f.contact_person
'
            );
        }

        if ($this->categoryExists()) {
            $this->connection->query('
INSERT INTO OfferCategoryAssociation(offer_id, category_id)
SELECT f.id, f.category
FROM mm_ferienpass f
INNER JOIN mm_ferienpass_category c ON c.id=f.category
'
            );
        }

        $schemaManager = $this->connection->getSchemaManager();
        if ($schemaManager->tablesExist('mm_accessibility')) {
            $this->connection->query(<<<'SQL'
UPDATE Offer O inner join (
    SELECT Offer.id, CONCAT('[', GROUP_CONCAT(CONCAT('"', mm_accessibility.alias, '"')), ']') as joined
    FROM tl_metamodel_tag_relation
             INNER JOIN mm_accessibility ON mm_accessibility.id = tl_metamodel_tag_relation.value_id
             INNER JOIN Offer ON Offer.id = tl_metamodel_tag_relation.item_id
    WHERE tl_metamodel_tag_relation.att_id = 34
    GROUP BY tl_metamodel_tag_relation.item_id
) as A on O.id = A.id
set O.accessibility = joined
SQL
            );
        }

        return $this->createResult(true);
    }

    private function categoryExists(): bool
    {
        try {
            $this->connection->query('SELECT category FROM mm_ferienpass');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function contactPersonExists(): bool
    {
        try {
            $this->connection->query('SELECT contact_person FROM mm_ferienpass');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function applicationNotesExist(): bool
    {
        try {
            $this->connection->query('SELECT application_notes FROM mm_ferienpass');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function bringExists(): bool
    {
        try {
            $this->connection->query('SELECT bring FROM mm_ferienpass');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function aktivPassExists(): bool
    {
        try {
            $this->connection->query('SELECT aktivpass FROM mm_ferienpass');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function applicationDeadlineExists(): bool
    {
        try {
            $this->connection->query('SELECT application_deadline FROM mm_ferienpass');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function requiresApplicationExists(): bool
    {
        try {
            $this->connection->query('SELECT requires_application FROM mm_ferienpass');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function commentExists(): bool
    {
        try {
            $this->connection->query('SELECT comment FROM mm_ferienpass');

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
