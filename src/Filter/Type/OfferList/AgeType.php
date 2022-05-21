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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class AgeType implements OfferListFilterType
{
    public static function getName(): string
    {
        return 'age';
    }

    public function applyFilter(QueryBuilder $qb, FormInterface $form)
    {
        $k = $form->getName();
        $v = $form->getData();

        $qb
            ->andWhere($qb->expr()->andX('o.minAge IS NULL OR o.minAge = 0 OR o.minAge <= :q_'.$k, 'o.maxAge IS NULL OR o.maxAge = 0 OR o.maxAge >= :q_'.$k))
            ->setParameter('q_'.$k, $v, ParameterType::INTEGER)
        ;
    }

    public function typeGuess(): ?TypeGuess
    {
        return new TypeGuess(IntegerType::class, ['label' => 'Alter'], Guess::HIGH_CONFIDENCE);
    }

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        return new TranslatableMessage('offerList.filter.age', ['value' => $form->getViewData()]);
    }
}
