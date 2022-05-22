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
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class NameType extends AbstractType implements OfferFilterType
{
    public static function getName(): string
    {
        return 'name';
    }

    public function getParent(): string
    {
        return SearchType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Nach Titel suchen',
            'required' => false,
        ]);
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

    public function getViewData(FormInterface $form): ?TranslatableInterface
    {
        return new TranslatableMessage('offerList.filter.name', ['value' => $form->getViewData()]);
    }

    public function getBlockPrefix(): string
    {
        return 'filter_name';
    }
}
