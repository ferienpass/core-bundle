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

namespace Ferienpass\CoreBundle\Filter;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Ferienpass\CoreBundle\Form\ListFiltersType;
use Symfony\Component\Form\FormFactoryInterface;

class OfferListFilterFactory
{
    private array $filterTypes = [];

    public function __construct(private readonly FormFactoryInterface $formFactory, iterable $filterTypes)
    {
        $this->filterTypes = $filterTypes instanceof \Traversable ? iterator_to_array($filterTypes, true) : $this->filterTypes;
    }

    public function create(DoctrineQueryBuilder $queryBuilder): OfferListFilter
    {
        return new OfferListFilter($this->formFactory->create(ListFiltersType::class), $queryBuilder, $this->filterTypes);
    }
}
