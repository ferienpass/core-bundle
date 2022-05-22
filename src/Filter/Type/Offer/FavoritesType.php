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

use Doctrine\ORM\QueryBuilder;
use Ferienpass\CoreBundle\Filter\Type\OfferFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class FavoritesType extends AbstractType implements OfferFilterType
{
    public function __construct(private Session $session)
    {
    }

    public static function getName(): string
    {
        return 'favorites';
    }

    public function getParent(): string
    {
        return CheckboxType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'nur gespeicherte',
            'false_values' => ['', null],
            'required' => false,
        ]);
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

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        return new TranslatableMessage('offerList.filter.favorites');
    }

    public function getBlockPrefix(): string
    {
        return 'filter_favorites';
    }
}
