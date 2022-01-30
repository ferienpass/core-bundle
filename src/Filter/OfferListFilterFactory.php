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
use Ferienpass\CoreBundle\Form\OfferFiltersType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class OfferListFilterFactory
{
    public function __construct(private FormFactoryInterface $formFactory, private Session $session)
    {
    }

    public function create(DoctrineQueryBuilder $queryBuilder): OfferListFilter
    {
        return new OfferListFilter($this->formFactory->create(OfferFiltersType::class), $this->session, $queryBuilder);
    }
}
