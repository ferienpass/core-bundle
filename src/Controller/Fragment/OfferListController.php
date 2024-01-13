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

use Contao\CoreBundle\Controller\AbstractController;
use Doctrine\DBAL\Types\Types;
use Ferienpass\CoreBundle\Filter\OfferListFilterFactory;
use Ferienpass\CoreBundle\Pagination\Paginator;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class OfferListController extends AbstractController
{
    public function __construct(private EditionRepository $editionRepository, private OfferRepository $offerRepository, private OfferListFilterFactory $filterFactory)
    {
    }

    public function __invoke(Request $request, Session $session): Response
    {
        $qb = $this->offerRepository->createQueryBuilder('o')->where('o.published = 1');
        $hasEditions = $this->editionRepository->count([]) > 0;
        $pageModel = $request->attributes->get('pageModel');
        $edition = $this->editionRepository->findOneToShow($pageModel);

        if ($hasEditions && null !== $edition) {
            $qb->andWhere('o.edition = :edition')->setParameter('edition', $edition->getId(), Types::INTEGER);
        }

        if ($hasEditions && null === $edition) {
            return $this->render('@FerienpassCore/Fragment/offer_list.html.twig');
        }

        $qb->leftJoin('o.dates', 'dates');

        if ('Restplätze' === $pageModel->title) {
            $qb
                ->andWhere('o.onlineApplication = 1')
                ->andWhere($qb->expr()->orX($qb->expr()->isNull('o.applicationDeadline'), 'o.applicationDeadline > CURRENT_TIMESTAMP()'))
                ->leftJoin('o.attendances', 'a')
                ->addSelect('COUNT(a.id) as HIDDEN attCount')
                ->groupBy('o.id', 'a.status', 'o.maxParticipants')
                ->having('a.status = :status_confirmed')
                ->andHaving($qb->expr()->orX($qb->expr()->isNull('o.maxParticipants'), 'o.maxParticipants < 1', 'o.maxParticipants > attCount'))
                ->setParameter('status_confirmed', 'confirmed')

                ->andWhere($qb->expr()->orX()->add('dates IS NULL')->add('dates.begin >= CURRENT_TIMESTAMP()'))
                ->andWhere('o.cancelled <> 1')
            ;
        }

        $filter = $this->filterFactory->create($qb)->applyFilter($request->query->all());

        $qb->orderBy('dates.begin');

        $paginator = (new Paginator($qb))->paginate($request->query->getInt('page', 1));

        return $this->render('@FerienpassCore/Fragment/offer_list.html.twig', [
            'edition' => $edition ?? null,
            'filter' => $filter,
            'pagination' => $paginator,
        ]);
    }
}
