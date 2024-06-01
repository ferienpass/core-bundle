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
use Ferienpass\CoreBundle\Entity\Participant\BaseParticipant;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Repository\ParticipantRepositoryInterface;

class EraseDataFacade
{
    public function __construct(private readonly Connection $connection, private readonly ParticipantRepositoryInterface $participants, private readonly EntityManagerInterface $doctrine)
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
SELECT DISTINCT p.id, p.lastname
FROM Participant p
         LEFT JOIN Attendance a ON p.id = a.participant_id
         LEFT JOIN Offer f ON f.id = a.offer_id
         LEFT JOIN Edition e ON e.id = f.edition
         LEFT JOIN EditionTask et ON e.id = et.pid
WHERE
   (f.id IS NULL AND (p.createdAt IS NULL OR p.createdAt < DATE_SUB(NOW(), INTERVAL 12 WEEK)))
   OR
      (et.type = 'show_offers' AND et.periodEnd < DATE_SUB(NOW(), INTERVAL 12 WEEK))
ORDER BY p.lastname
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
         LEFT JOIN User u ON u.id = p.member_id
WHERE et.periodEnd > NOW() OR d.end > NOW() OR (u.dontDeleteBefore IS NOT NULL AND u.dontDeleteBefore > NOW())
SQL
        )->fetchAllNumeric();

        $participantsToDelete = array_column($participantsToDelete, 0);
        $participantsToKeep = array_column($participantsToKeep, 0);

        return $this->participants->findBy(['id' => array_diff($participantsToDelete, $participantsToKeep)]);
    }

    private function deleteParticipants(): void
    {
        $participants = $this->expiredParticipants();

        /** @var BaseParticipant $participant */
        foreach ($participants as $participant) {
            /** @var Attendance $attendance */
            $pseudonym = bin2hex(random_bytes(5));
            foreach ($participant->getAttendances() as $attendance) {
                // Create a pseudonym for each participant
                $attendance->setParticipantPseudonym($pseudonym);

                // Remove parent association so attendances do not get removed
                $attendance->unsetParticipant();
            }
        }

        $this->doctrine->flush();

        $this->participants
            ->createQueryBuilder('p')
            ->delete()
            ->where('p IN (:ids)')
            ->setParameter('ids', $participants)
            ->getQuery()
            ->execute()
        ;
    }

    private function deleteMembersWithNoParticipants(): void
    {
        $userIds = $this->doctrine->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('u.id')
            ->leftJoin('u.participants', 'p')
            ->where('p.id IS NULL')
            // ->andWhere('u.lastLogin < DATE_SUB(NOW(), INTERVAL 2 WEEK)')
            ->andWhere("JSON_SEARCH(u.roles, 'one', :role_member) IS NOT NULL")
            ->andWhere("JSON_SEARCH(u.roles, 'one', :role_host) IS NULL")
            ->andWhere("JSON_SEARCH(u.roles, 'one', :role_admin) IS NULL")
            ->andWhere("JSON_SEARCH(u.roles, 'one', :role_sadmin) IS NULL")
            ->andWhere('u.dontDeleteBefore IS NULL OR u.dontDeleteBefore < CURRENT_TIMESTAMP()')
            ->setParameter('role_member', 'ROLE_MEMBER')
            ->setParameter('role_host', 'ROLE_HOST')
            ->setParameter('role_admin', 'ROLE_ADMIN')
            ->setParameter('role_sadmin', 'ROLE_SUPER_ADMIN')
            ->getQuery()
            ->getSingleColumnResult()
        ;

        $this->doctrine->getRepository(User::class)
            ->createQueryBuilder('u')
            ->delete()
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $userIds, ArrayParameterType::INTEGER)
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
