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

namespace Ferienpass\CoreBundle\Filter\Type\Offer;

use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\QueryBuilder;
use Ferienpass\CoreBundle\Filter\Type\OfferFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class FeeType extends AbstractType implements OfferFilterType
{
    public static function getName(): string
    {
        return 'fee';
    }

    public function getParent(): string
    {
        return MoneyType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'max. Kosten',
            'divisor' => 100,
            'required' => false,
        ]);
    }

    public function applyFilter(QueryBuilder $qb, FormInterface $form)
    {
        $k = $form->getName();
        $v = $form->getData();

        $qb
            ->andWhere($qb->expr()->andX('o.fee IS NULL OR o.fee = 0 OR o.fee <= :q_'.$k))
            ->setParameter('q_'.$k, $v, ParameterType::INTEGER)
        ;
    }

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        return new TranslatableMessage('offerList.filter.fee', ['value' => number_format($form->getViewData(), 2, ',', '.')]);
    }

    public function getBlockPrefix(): string
    {
        return 'filter_fee';
    }
}
