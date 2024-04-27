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

use Doctrine\ORM\Query\Expr;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;

class OfferRepository extends EntityRepository implements OfferRepositoryInterface
{
    public function findByAlias(string $alias): ?OfferInterface
    {
        return $this->findOneBy(['alias' => $alias]);
    }

    public function createCopy(OfferInterface $original): OfferInterface
    {
        $new = $this->createNew();

        $new->setName($original->getName().' (Kopie)');
        $new->setDescription($original->getDescription());
        $new->setMeetingPoint($original->getMeetingPoint());
        $new->setBring($original->getBring());
        $new->setMinParticipants($original->getMinParticipants());
        $new->setMaxParticipants($original->getMaxParticipants());
        $new->setMinAge($original->getMinAge());
        $new->setMaxAge($original->getMaxAge());
        $new->setRequiresApplication($original->requiresApplication());
        $new->setOnlineApplication($original->isOnlineApplication());
        $new->setApplyText($original->getApplyText());
        $new->setContactUser($original->getContactUser());
        $new->setFee($original->getFee());
        $new->setImage($original->getImage());
        foreach ($original->getHosts() as $host) {
            $new->addHost($host);
        }

        return $new;
    }

    public function createVariant(OfferInterface $original): OfferInterface
    {
        $new = $this->createCopy($original);
        $new->setVariantBase($original);

        return $new;
    }

    public function updateVariant(OfferInterface $variant): void
    {
        $base = $variant->getVariantBase();

        $variant->setName($base->getName());
        $variant->setDescription($base->getDescription());
        $variant->setMeetingPoint($base->getMeetingPoint());
        $variant->setBring($base->getBring());
        $variant->setMinParticipants($base->getMinParticipants());
        $variant->setMaxParticipants($base->getMaxParticipants());
        $variant->setMinAge($base->getMinAge());
        $variant->setMaxAge($base->getMaxAge());
        $variant->setRequiresApplication($base->requiresApplication());
        $variant->setOnlineApplication($base->isOnlineApplication());
        $variant->setApplyText($base->getApplyText());
        $variant->setContactUser($base->getContactUser());
        $variant->setFee($base->getFee());
        $variant->setImage($base->getImage());
    }

    public function findUpcoming(): array
    {
        $qb0 = $this->_em->createQueryBuilder();
        $qb = $this->createQueryBuilder('offer')
            ->innerJoin(
                'offer.dates',
                'date',
                Expr\Join::WITH,
                $qb0->expr()->andX(
                    $qb0->expr()->gt('date.begin', 'CURRENT_TIMESTAMP()')
                )
            )
            ->orderBy('date.begin', 'ASC')
            ->getQuery();

        return $qb->getResult();
    }
}
