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
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'label' => 'MSC.oldPassword',
                'translation_domain' => 'contao_default',
                'required' => true,
                'attr' => ['autocomplete' => 'current-password'],
                'constraints' => [
                    new NotBlank(),
                    new UserPassword(['message' => 'Das alte Passwort ist nicht korrekt']),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'ERR.passwordMatch',
                'translation_domain' => 'contao_default',
                'required' => true,
                'first_options' => ['label' => 'MSC.newPassword'],
                'second_options' => ['label' => 'MSC.confirmation'],
                'constraints' => [
                    new Length(['min' => Config::get('minPasswordLength'), 'minMessage' => str_replace('%d', '{{ limit }}', 'Ein Passwort muss mindestens %d Zeichen lang sein!')]),
                ],
            ])
            ->add('request_token', ContaoRequestTokenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'MSC.changePassword',
                'translation_domain' => 'contao_default',
            ])
        ;
    }
}
