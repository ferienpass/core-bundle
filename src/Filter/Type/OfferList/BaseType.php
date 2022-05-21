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

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\QueryBuilder;
use Ferienpass\CoreBundle\Filter\Type\OfferListFilterType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Contracts\Translation\TranslatableInterface;

class BaseType implements OfferListFilterType
{
    public static function getName(): string
    {
        return 'base';
    }

    public function applyFilter(QueryBuilder $qb, FormInterface $form)
    {
        $k = $form->getName();
        $v = $form->getData();

        $qb
            ->andWhere($qb->expr()->orX()->add('o.id = :q_'.$k)->add('o.variantBase = :q_'.$k))
            ->setParameter('q_'.$k, $v, ParameterType::INTEGER)
        ;
    }

    public function typeGuess(): ?TypeGuess
    {
        return null;
    }

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        return null;
    }
}
