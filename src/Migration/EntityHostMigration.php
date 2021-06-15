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
use Contao\StringUtil;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Repository\HostRepository;

class EntityHostMigration extends AbstractMigration
{
    private Connection      $connection;
    private ManagerRegistry $doctrine;
    private HostRepository $hostRepository;

    public function __construct(Connection $connection, ManagerRegistry $doctrine, HostRepository $hostRepository)
    {
        $this->connection = $connection;
        $this->doctrine = $doctrine;
        $this->hostRepository = $hostRepository;
    }

    public function getName(): string
    {
        return 'Migrate to Host entity';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (null === $schemaManager || !$schemaManager->tablesExist(['mm_host', 'Host', 'HostMemberAssociation'])) {
            return false;
        }

        return $this->connection->query('SELECT id FROM mm_host')->rowCount() > 0
               && 0 === $this->connection->query('SELECT id FROM Host')->rowCount();
    }

    public function run(): MigrationResult
    {
        $this->connection->query(
            '
INSERT INTO Host(id, tstamp, `name`, alias, email, website, phone, fax, street, postal, city, `text`, logo)
SELECT id, tstamp, `name`, alias, email, website, phone, fax, street, postal, city, introduction, logo
FROM mm_host
'
        );

        $this->connection->query('
INSERT INTO HostMemberAssociation(host_id, member_id)
SELECT h.id, m.id
FROM tl_member_to_host m2h
INNER JOIN Host h ON h.id = m2h.host_id
INNER JOIN tl_member m ON m.id = m2h.member_id
'
        );

        $em = $this->doctrine->getManager();

        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->startsWith('website', 'a:2'));

        foreach ($this->hostRepository->matching($criteria) as $host) {
            [,$website] = StringUtil::deserialize($host->getWebsite(), true);

            $host->setWebsite($website);
            $em->persist($host);
        }

        $em->flush();

        return $this->createResult(true);
    }
}
