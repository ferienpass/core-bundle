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
use Ferienpass\CoreBundle\Entity\OfferCategory;
use Ferienpass\CoreBundle\Filter\Type\OfferListFilterType;
use Ferienpass\CoreBundle\Form\SimpleType\FilterCategoryType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class CategoryType implements OfferListFilterType
{
    public static function getName(): string
    {
        return 'category';
    }

    public function applyFilter(QueryBuilder $qb, FormInterface $form)
    {
        $v = $form->getData();

        $qb->andWhere($qb->expr()->orX(...array_map(fn ($i) => ':q_'.$i.' MEMBER OF o.categories', array_keys($v->toArray()))));
        foreach ($v as $i => $cat) {
            $qb->setParameter('q_'.$i, $cat);
        }
    }

    public function typeGuess(): TypeGuess
    {
        return new TypeGuess(FilterCategoryType::class, [], Guess::HIGH_CONFIDENCE);
    }

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        $value = implode(', ', array_map(fn (OfferCategory $c) => $c->getName(), $form->getData()->toArray()));

        return new TranslatableMessage('offerList.filter.category', ['value' => $value]);
    }
}
