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
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class NameType implements OfferListFilterType
{
    public static function getName(): string
    {
        return 'name';
    }

    public function applyFilter(QueryBuilder $qb, FormInterface $form)
    {
        $k = $form->getName();
        $v = $form->getData();

        $qb
            ->andWhere('o.name LIKE :q_'.$k)
            ->setParameter('q_'.$k, '%'.addcslashes($v, '%_').'%', ParameterType::STRING)
        ;
    }

    public function typeGuess(): TypeGuess
    {
        return new TypeGuess(SearchType::class, ['label' => 'Nach Titel suchen'], Guess::HIGH_CONFIDENCE);
    }

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        return new TranslatableMessage('offerList.filter.name', ['value' => $form->getViewData()]);
    }
}
