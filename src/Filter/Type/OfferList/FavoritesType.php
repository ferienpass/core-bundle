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

namespace Ferienpass\CoreBundle\Filter\Type\OfferList;

use Doctrine\ORM\QueryBuilder;
use Ferienpass\CoreBundle\Filter\Type\OfferListFilterType;
use Ferienpass\CoreBundle\Form\SimpleType\FilterFavoritesType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class FavoritesType implements OfferListFilterType
{
    public function __construct(private Session $session)
    {
    }

    public static function getName(): string
    {
        return 'favorites';
    }

    public function applyFilter(QueryBuilder $qb, FormInterface $form)
    {
        $k = $form->getName();
        $savedOffers = $this->session->isStarted() ? $this->session->get('saved_offers') : [];

        $qb
            ->andWhere('o.id IN (:q_'.$k.')')
            ->setParameter('q_'.$k, $savedOffers)
        ;
    }

    public function typeGuess(): TypeGuess
    {
        return new TypeGuess(FilterFavoritesType::class, [], Guess::HIGH_CONFIDENCE);
    }

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        return new TranslatableMessage('offerList.filter.favorites');
    }
}
