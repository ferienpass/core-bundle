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

use Ferienpass\CmsBundle\Form\SimpleType\ContaoRequestTokenType;
use Ferienpass\CoreBundle\Entity\User;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserPersonalDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'tl_member.firstname.0',
                'translation_domain' => 'contao_tl_member',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'tl_member.lastname.0',
                'translation_domain' => 'contao_tl_member',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'tl_member.email.0',
                'translation_domain' => 'contao_tl_member',
                'attr' => [
                    'placeholder' => 'email@beispiel.de',
                ],
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ])

            ->add('phone', TelType::class, [
                'label' => 'tl_member.phone.0',
                'translation_domain' => 'contao_tl_member',
                'constraints' => [
                    new PhoneNumber(['type' => PhoneNumber::FIXED_LINE, 'defaultRegion' => 'DE']),
                ],
                'attr' => [
                    'placeholder' => '030-00000',
                ],
            ])
            ->add('mobile', TelType::class, [
                'label' => 'tl_member.mobile.0',
                'translation_domain' => 'contao_tl_member',
                'constraints' => [
                    new PhoneNumber(['type' => PhoneNumber::MOBILE, 'defaultRegion' => 'DE']),
                ],
                'attr' => [
                    'placeholder' => '0172-0000000',
                ],
            ])

            ->add('street', TextType::class, [
                'label' => 'tl_member.street.0',
                'translation_domain' => 'contao_tl_member',
            ])
            ->add('postal', TextType::class, [
                'label' => 'tl_member.postal.0',
                'translation_domain' => 'contao_tl_member',
            ])
            ->add('city', TextType::class, [
                'label' => 'tl_member.city.0',
                'translation_domain' => 'contao_tl_member',
            ])
            ->add('country', CountryType::class, [
                'label' => 'tl_member.country.0',
                'translation_domain' => 'contao_tl_member',
                'preferred_choices' => ['DE'],
            ])

            ->add('request_token', ContaoRequestTokenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Daten speichern',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'required' => false,
            'label_format' => 'user.label.%name%',
            'translation_domain' => 'cms',
            'csrf_protection' => false,
        ]);
    }
}
