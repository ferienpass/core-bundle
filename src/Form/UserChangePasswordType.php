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

use Contao\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserChangePasswordType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label_format' => 'participants.label.%name%',
            'translation_domain' => 'contao_default',
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => 'MSC.oldPassword',
                'required' => true,
                'width' => '2/3',
                'attr' => ['autocomplete' => 'current-password'],
                'constraints' => [
                    new NotBlank(),
                    new UserPassword(['message' => 'Das alte Passwort ist nicht korrekt']),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'ERR.passwordMatch',
                'required' => true,
                'first_options' => ['label' => 'MSC.newPassword',                'width' => '1/2'],
                'second_options' => ['label' => 'MSC.confirmation',                'width' => '1/2'],
                'constraints' => [
                    new Length(['min' => Config::get('minPasswordLength'), 'minMessage' => str_replace('%d', '{{ limit }}', 'Ein Passwort muss mindestens %d Zeichen lang sein!')]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'MSC.changePassword',
            ])
        ;
    }
}
