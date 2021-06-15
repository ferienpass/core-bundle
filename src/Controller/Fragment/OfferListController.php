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
use Contao\PageModel;
use Doctrine\DBAL\Types\Types;
use Ferienpass\CoreBundle\Form\OfferFiltersType;
use Ferienpass\CoreBundle\Pagination\Paginator;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class OfferListController extends AbstractController
{
    private EditionRepository $editionRepository;
    private OfferRepository $offerRepository;

    public function __construct(EditionRepository $passEditionRepository, OfferRepository $offerRepository)
    {
        $this->editionRepository = $passEditionRepository;
        $this->offerRepository = $offerRepository;
    }

    public function __invoke(Request $request, Session $session): Response
    {
        $qb = $this->offerRepository->createQueryBuilder('o')
            ->andWhere('o.published = 1')
        ;

        if ($hasEditions = ($this->editionRepository->count([]) > 0)) {
            $edition = $this->editionRepository->findOneToShow(PageModel::findByPk($request->get('pageModel')));

            $qb->andWhere('o.edition = :edition')->setParameter('edition', $edition->getId(), Types::INTEGER);
        }

        if ($hasEditions && null === ($edition ?? null)) {
            return $this->render('@FerienpassCore/Fragment/offer_list.html.twig');
        }

        $qb->leftJoin('o.dates', 'dates');

        $filters = $this->createForm(OfferFiltersType::class);
        $filters->handleRequest($request);
        foreach ($request->query as $k => $item) {
            if ($filters->has($k) && $v = $filters->get($k)->getData()) {
                switch ($k) {
                    case 'name':
                        $qb
                            ->andWhere('o.name LIKE :q_'.$k)
                            ->setParameter('q_'.$k, '%'.addcslashes($v, '%_').'%')
                        ;
                        break;
                    case 'favorites':
                        $savedOffers = $session->isStarted()
                            ? $session->get('saved_offers')
                            : [];

                        $qb
                            ->andWhere('o.id IN (:q_'.$k.')')
                            ->setParameter('q_'.$k, $savedOffers)
                        ;
                        break;
                    case 'fee':
                        $qb
                            ->andWhere('o.fee <= :q_'.$k)
                            ->setParameter('q_'.$k, $v)
                        ;
                        break;
                    case 'age':
                        $qb
                            ->andWhere($qb->expr()->andX('o.minAge IS NULL OR o.minAge = 0 OR o.minAge <= :q_'.$k, 'o.maxAge IS NULL OR o.maxAge = 0 OR o.maxAge >= :q_'.$k))
                            ->setParameter('q_'.$k, $v)
                        ;
                        break;
                    case 'category':
                        $qb->andWhere($qb->expr()->orX(...array_map(fn ($i) => ':q_'.$i.' MEMBER OF o.categories', array_keys($v->toArray()))));
                        foreach ($v as $i => $cat) {
                            $qb->setParameter('q_'.$i, $cat);
                        }

                        break;
                    case 'base':
                        $qb
                            ->andWhere($qb->expr()->orX()->add('o.id = :q_'.$k)->add('o.variantBase = :q_'.$k))
                            ->setParameter('q_'.$k, $v)
                        ;

                        break;
                    case 'earliest_date':
                        $qb
                            ->andWhere($qb->expr()->orX()->add('dates IS NULL')->add('dates.begin >= :q_'.$k))
                            ->setParameter('q_'.$k, $v)
                        ;
                        break;
                    case 'latest_date':
                        \assert($v instanceof \DateTime);
                        // < DATE() +1 day has the same effect as <= DATE() 23:59:59
                        $v->modify('+1 day');
                        $qb
                            ->andWhere($qb->expr()->orX()->add('dates IS NULL')->add('dates.end <= :q_'.$k))
                            ->setParameter('q_'.$k, $v, Types::DATE_MUTABLE)
                        ;
                }
            }
        }

        $qb->orderBy('dates.begin');

        $paginator = (new Paginator($qb))->paginate($request->query->getInt('page', 1));

        return $this->render('@FerienpassCore/Fragment/offer_list.html.twig', [
            'edition' => $edition ?? null,
            'filters' => $filters->createView(),
            'pagination' => $paginator,
        ]);
    }
}
