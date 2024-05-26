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

namespace Ferienpass\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\AgreementLetterSignature;
use Ferienpass\CoreBundle\Entity\Edition;

class AgreementLetterSignaturesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgreementLetterSignature::class);
    }

    public function findValidForEdition(Edition $edition): ?AgreementLetterSignature
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.edition = :edition')
            ->andWhere('a.hash = :hash')
            ->andWhere('a.validUntil IS NULL OR a.validUntil >= CURRENT_TIMESTAMP()')
            ->setParameter('edition', $edition)
            ->setParameter('hash', md5($edition->getAgreementLetterText()))
            ->orderBy('a.signedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
