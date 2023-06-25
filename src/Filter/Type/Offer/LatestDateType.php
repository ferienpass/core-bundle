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

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class LatestDateType extends AbstractOfferFilterType
{
    public static function getName(): string
    {
        return 'latest_date';
    }

    public function getParent(): string
    {
        return DateType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'label' => 'spätestes Datum',
            'widget' => 'single_text',
        ]);
    }

    public function applyFilter(QueryBuilder $qb, FormInterface $form)
    {
        $k = $form->getName();
        $v = $form->getData();

        \assert($v instanceof \DateTime);

        // < DATE() +1 day has the same effect as <= DATE() 23:59:59
        $v->modify('+1 day');
        $qb
            ->andWhere($qb->expr()->orX()->add('dates IS NULL')->add('dates.end <= :q_'.$k))
            ->setParameter('q_'.$k, $v, Types::DATE_MUTABLE)
        ;
    }

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        $date = $form->getData();
        if (!$date instanceof \DateTimeInterface) {
            return null;
        }

        return new TranslatableMessage('offerList.filter.latest_date', ['value' => $date->format('d.m.Y')]);
    }

    public function getBlockPrefix(): string
    {
        return 'filter_latest_date';
    }
}
