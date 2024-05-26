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
use Ferienpass\CoreBundle\Entity\HostConsent;
use Ferienpass\CoreBundle\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class HostConsentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, #[Autowire(param: 'ferienpass_admin.privacy_consent_text')] private readonly string $consentText)
    {
        parent::__construct($registry, HostConsent::class);
    }

    public function findValid(User $user): ?HostConsent
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.user = :user')
            ->andWhere('a.hash = :hash')
            ->andWhere('a.validUntil IS NULL OR a.validUntil >= CURRENT_TIMESTAMP()')
            ->setParameter('user', $user)
            ->setParameter('hash', md5($this->consentText))
            ->orderBy('a.signedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
