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

namespace Ferienpass\CoreBundle\Facade;

use Doctrine\DBAL\Connection;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Repository\ParticipantRepository;

class EraseDataFacade
{
    public function __construct(private Connection $connection, private ParticipantRepository $participantRepository)
    {
    }

    public function eraseData(): void
    {
        // Retain participant age for statistics
        $this->retainParticipantAge();

        // Delete all participants that have attendances on events with past holiday
        $this->deleteParticipants();

        // Delete parents that have no participants and haven't logged in since a while
        $this->deleteMembersWithNoParticipants();
    }

    public function expiredParticipants(): array
    {
        $participantsToDelete = $this->connection->executeQuery(
            <<<'SQL'
SELECT DISTINCT p.id
FROM Participant p
         LEFT JOIN Attendance a ON p.id = a.participant_id
         LEFT JOIN Offer f ON f.id = a.offer_id
         LEFT JOIN Edition e ON e.id = f.edition
         LEFT JOIN EditionTask et ON e.id = et.pid
WHERE
   (f.id IS NULL AND FROM_UNIXTIME(p.tstamp) < DATE_SUB(NOW(), INTERVAL 2 WEEK))
   OR
      (et.type = 'show_offers' AND et.periodEnd < DATE_SUB(NOW(), INTERVAL 2 WEEK))
SQL
        )->fetchAllNumeric();

        // Participants with attendances on events having a non-finished task
        $participantsToKeep = $this->connection->executeQuery(
            <<<'SQL'
SELECT DISTINCT p.id
FROM Participant p
         INNER JOIN Attendance a ON p.id = a.participant_id
         INNER JOIN Offer f ON f.id = a.offer_id
         INNER JOIN Edition e ON e.id = f.edition
         INNER JOIN EditionTask et ON e.id = et.pid
         LEFT JOIN OfferDate d ON d.offer_id = f.id
WHERE et.periodEnd > NOW() OR d.end > NOW()
SQL
        )->fetchAllNumeric();

        $participantsToDelete = array_column($participantsToDelete, 0);
        $participantsToKeep = array_column($participantsToKeep, 0);

        return $this->participantRepository->findBy(['id' => array_diff($participantsToDelete, $participantsToKeep)]);
    }

    private function deleteParticipants(): void
    {
        $participantIds = array_map(fn (Participant $participant) => $participant->getId(), $this->expiredParticipants());

        $this->connection->executeQuery(<<<'SQL'
DELETE l, r, n
FROM EventLog l
INNER JOIN EventLogRelated r ON r.log_id = l.id
LEFT JOIN NotificationLog n ON n.log_id = l.id
INNER JOIN Attendance a ON a.id = r.relatedId
WHERE r.relatedTable = 'Attendance'
  AND a.participant_id IN (?)
SQL
, [$participantIds], [Connection::PARAM_INT_ARRAY]);

        // Retain participant ids for statistics
        $this->connection->createQueryBuilder()
            ->update('Attendance')
            ->set('participant_id_original', 'participant_id')
            ->where('participant_id IN (:ids)')
            ->setParameter('ids', $participantIds, Connection::PARAM_INT_ARRAY)
            ->executeStatement()
        ;

        $this->connection->createQueryBuilder()
            ->update('Attendance')
            ->set('participant_id', 'NULL')
            ->where('participant_id IN (:ids)')
            ->setParameter('ids', $participantIds, Connection::PARAM_INT_ARRAY)
            ->executeStatement()
        ;

        $this->connection->createQueryBuilder()
            ->delete('Participant')
            ->where('id IN (:ids)')
            ->setParameter('ids', $participantIds, Connection::PARAM_INT_ARRAY)
            ->executeStatement()
        ;
    }

    private function deleteMembersWithNoParticipants(): void
    {
        $members = $this->connection->executeQuery(
            <<<'SQL'
SELECT m.id
FROM tl_member m
         LEFT JOIN Participant p ON p.member_id = m.id
WHERE p.id IS NULL
  AND m.lastLogin < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 WEEK))
  AND m.`groups` = 'a:1:{i:0;s:1:"2";}'
SQL
        )->fetchAllNumeric();

        $members = array_column($members, 0);

        $this->connection->createQueryBuilder()
            ->delete('tl_version')
            ->where("fromTable = 'tl_member'")
            ->andWhere('pid IN (:ids)')
            ->setParameter('ids', $members, Connection::PARAM_INT_ARRAY)
            ->executeStatement();

        $this->connection->createQueryBuilder()
            ->delete('tl_member')
            ->where('id IN (:ids)')
            ->setParameter('ids', $members, Connection::PARAM_INT_ARRAY)
            ->executeStatement()
        ;
    }

    private function retainParticipantAge(): void
    {
        $this->connection->executeQuery(
            <<<'SQL'
UPDATE Attendance a
INNER JOIN Participant p ON a.participant_id = p.id
INNER JOIN Offer f ON a.offer_id = f.id
LEFT OUTER JOIN OfferDate d ON d.offer_id = f.id
SET age = (IF((p.dateOfBirth IS NULL), null, TIMESTAMPDIFF(YEAR, p.dateOfBirth, d.begin)))
WHERE a.age IS NULL
SQL
        )->rowCount();
    }
}
