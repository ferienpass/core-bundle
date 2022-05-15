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

namespace Ferienpass\CoreBundle\Form;

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Ferienpass\CoreBundle\Form\SimpleType\FilterCategoryType;
use Ferienpass\CoreBundle\Form\SimpleType\FilterFavoritesType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (empty($options['attributes']) || \in_array('name', $options['attributes'], true)) {
            $builder->add('name', SearchType::class, [
                'label' => 'Nach Titel suchen',
                'required' => false,
            ]);
        }

        if (empty($options['attributes']) || \in_array('fee', $options['attributes'], true)) {
            $builder->add('fee', MoneyType::class, [
                'label' => 'max. Kosten',
                'required' => false,
                'divisor' => 100,
            ]);
        }

        if (empty($options['attributes']) || \in_array('age', $options['attributes'], true)) {
            $builder->add('age', IntegerType::class, [
                'label' => 'Alter',
                'required' => false,
            ]);
        }

        if (empty($options['attributes']) || \in_array('favorites', $options['attributes'], true)) {
            $builder->add('favorites', FilterFavoritesType::class, [
                'auto_submit' => !empty($options['attributes']),
            ]);
        }

        if (empty($options['attributes']) || \in_array('category', $options['attributes'], true)) {
            $builder->add('category', FilterCategoryType::class);
        }

//        if (empty($options['attributes']) || \in_array('base', $options['attributes'], true)) {
//            $builder->add('base', EntityType::class, [
//                'required' => false,
//                'class' => Offer::class,
//                'choice_label' => 'name',
//            ]);
//        }

        if (empty($options['attributes']) || \in_array('earliest_date', $options['attributes'], true)) {
            $builder->add('earliest_date', DateType::class, [
                'label' => 'frühstes Datum',
                'required' => false,
                'widget' => 'single_text',
            ]);
        }

        if (empty($options['attributes']) || \in_array('latest_date', $options['attributes'], true)) {
            $builder->add('latest_date', DateType::class, [
                'label' => 'spätestes Datum',
                'required' => false,
                'widget' => 'single_text',
            ]);
        }

        if (empty($options['attributes'])) {
            $builder->add('submit', SubmitType::class, [
                'label' => 'Filter anwenden',
            ]);
        }

        $builder->add('request_token', ContaoRequestTokenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attributes' => [],
            'csrf_protection' => false,
        ]);

        $resolver->setDefined('attributes');
    }
}
