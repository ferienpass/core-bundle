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

use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'tl_member.firstname.0',
                'translation_domain' => 'contao_tl_member',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'tl_member.lastname.0',
                'translation_domain' => 'contao_tl_member',
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
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'MSC.password.0',
                'translation_domain' => 'contao_default',
                'help' => 'Ihr Passwort muss aus mindestens 8 Zeichen bestehen.',
                'constraints' => [
                    new NotBlank(),
                    new NotCompromisedPassword(),
                    new Length(['min' => 8]),
                ],
            ])
        ;

        if ($options['ask_mobile']) {
            $builder
                ->add('mobile', TelType::class, [
                    'label' => 'tl_member.mobile.0',
                    'translation_domain' => 'contao_tl_member',
                    'help' => 'Wir schicken Ihnen Zusagen per SMS.',
                    'constraints' => [
                        new PhoneNumber(['type' => PhoneNumber::MOBILE, 'defaultRegion' => 'DE']),
                    ],
                    'attr' => [
                        'placeholder' => '0172-0000000',
                    ],
                    'required' => false,
                ])
            ;
        }

        if ($options['ask_phone']) {
            $builder
                ->add('phone', TelType::class, [
                    'label' => 'tl_member.phone.0',
                    'translation_domain' => 'contao_tl_member',
                    'constraints' => [
                        new PhoneNumber(['type' => PhoneNumber::FIXED_LINE, 'defaultRegion' => 'DE']),
                    ],
                    'attr' => [
                        'placeholder' => '030-00000',
                    ],
                    'required' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'ask_phone' => true,
            'ask_mobile' => true,
            'csrf_protection' => false,
        ]);

        $resolver->setAllowedTypes('ask_phone', 'bool');
        $resolver->setAllowedTypes('ask_mobile', 'bool');
    }
}
