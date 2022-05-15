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

namespace Ferienpass\CoreBundle\Form\SimpleType;

use Ferienpass\CoreBundle\Entity\OfferCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class FilterCategoryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'Kategorie',
            'required' => false,
            'multiple' => true,
            'class' => OfferCategory::class,
            'choice_value' => fn (?OfferCategory $entity) => $entity ? $entity->getAlias() : '',
            'choice_label' => 'name',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
