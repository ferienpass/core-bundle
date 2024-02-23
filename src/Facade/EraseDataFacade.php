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

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Repository\ParticipantRepository;

class EraseDataFacade
{
    public function __construct(private readonly Connection $connection, private readonly ParticipantRepository $participantRepository, private readonly EntityManagerInterface $doctrine)
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
   (f.id IS NULL AND p.createdAt < DATE_SUB(NOW(), INTERVAL 2 WEEK))
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

        // Retain participant ids for statistics
        $this->doctrine->getRepository(Attendance::class)
            ->createQueryBuilder('a')
            ->update()
            ->set('participant_id_original', 'participant_id')
            ->where('participant_id IN (:ids)')
            ->setParameter('ids', $participantIds, ArrayParameterType::INTEGER)
            ->getQuery()
            ->execute()
        ;

        // Remove parent association, attendances do not get removed
        $this->doctrine->getRepository(Attendance::class)
            ->createQueryBuilder('a')
            ->update()
            ->set('participant_id', 'NULL')
            ->where('participant_id IN (:ids)')
            ->setParameter('ids', $participantIds, ArrayParameterType::INTEGER)
            ->getQuery()
            ->execute()
        ;

        $this->doctrine->getRepository(Participant::class)
            ->createQueryBuilder('p')
            ->delete()
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $participantIds, ArrayParameterType::INTEGER)
            ->getQuery()
            ->execute()
        ;
    }

    private function deleteMembersWithNoParticipants(): void
    {
        $this->doctrine->getRepository(User::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.participants', 'p')
            ->delete()
            ->where('p IS NULL')
            //->andWhere('u.lastLogin < DATE_SUB(NOW(), INTERVAL 2 WEEK)')
            ->andWhere("JSON_SEARCH(u.roles, 'one', :role_member) IS NOT NULL")
            ->andWhere("JSON_SEARCH(u.roles, 'one', :role_host) IS NULL")
            ->andWhere("JSON_SEARCH(u.roles, 'one', :role_admin) IS NULL")
            ->andWhere("JSON_SEARCH(u.roles, 'one', :role_sadmin) IS NULL")
            ->setParameter('role_member', 'ROLE_MEMBER')
            ->setParameter('role_host', 'ROLE_HOST')
            ->setParameter('role_admin', 'ROLE_ADMIN')
            ->setParameter('role_sadmin', 'ROLE_SUPER_ADMIN')
            ->getQuery()
            ->execute()
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
