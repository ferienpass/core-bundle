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

use Ferienpass\CoreBundle\Dto\Annotation\OfferFilterType as OfferFilterTypeAnnotation;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfferFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $properties = (new \ReflectionClass($options['data_class']))->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $annotations = array_merge(...array_map(fn (\ReflectionAttribute $attribute) => $attribute->getArguments(), $property->getAttributes(OfferFilterTypeAnnotation::class)));

            if (!$options['short'] || $annotations['shortForm']) {
                $builder->add($property->getName());
            }
        }

        if (!$options['short']) {
            $builder->add('submit', SubmitType::class, ['label' => 'Filter anwenden']);
        }

        $builder->add('request_token', ContaoRequestTokenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('csrf_protection', false);
        $resolver->setRequired('data_class');

        $resolver->setDefined('short');
        $resolver->setAllowedTypes('short', 'bool');
    }
}
