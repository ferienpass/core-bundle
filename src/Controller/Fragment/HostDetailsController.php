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

namespace Ferienpass\CoreBundle\Controller\Fragment;

use Doctrine\DBAL\Types\Types;
use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HostDetailsController extends AbstractController
{
    private OfferRepository $offerRepository;
    private EditionRepository $editionRepository;

    public function __construct(OfferRepository $offerRepository, EditionRepository $editionRepository)
    {
        $this->offerRepository = $offerRepository;
        $this->editionRepository = $editionRepository;
    }

    public function __invoke(Host $host, Request $request): Response
    {
        return $this->render('@FerienpassCore/Fragment/host_details.html.twig', [
            'host' => $host,
            'offers' => $this->fetchOffers($host),
        ]);
    }

    private function fetchOffers(Host $host): ?array
    {
        $editions = $this->editionRepository->findWithActiveTask('show_offers');

        $qb = $this->offerRepository->createQueryBuilder('o')
            ->leftJoin('o.dates', 'dates')
            ->innerJoin('o.hosts', 'hosts')
            ->andWhere('hosts.id = :host')->setParameter('host', $host->getId(), Types::INTEGER)
            ->andWhere('o.edition IN (:editions)')->setParameter('editions', $editions)
            ->orderBy('dates.begin')
        ;

        return $qb->getQuery()->getResult();
    }
}
