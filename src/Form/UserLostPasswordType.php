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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserLostPasswordType extends AbstractType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['reset_password']) {
            $builder
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => $this->translator->trans('ERR.passwordMatch', [], 'contao_default'),
                    'translation_domain' => 'contao_default',
                    'required' => true,
                    'first_options' => ['label' => 'MSC.newPassword', 'help' => 'Ihr Passwort muss aus mindestens 8 Zeichen bestehen.'],
                    'second_options' => ['label' => 'MSC.confirm.0', 'help' => 'MSC.confirm.1'],
                    'constraints' => [
                        new Length(['min' => Config::get('minPasswordLength'), 'minMessage' => str_replace('%d', '{{ limit }}', $this->translator->trans('ERR.passwordLength'))]),
                    ],
                ])
                ->add('submit', SubmitType::class, ['label' => 'MSC.setNewPassword', 'translation_domain' => 'contao_default'])
            ;
        } else {
            $builder
                ->add('email', EmailType::class, [
                    'label' => $this->translator->trans('MSC.username', [], 'contao_default'),
                    'constraints' => [
                        new NotBlank(),
                        new Email(),
                    ],
                ])
                ->add('submit', SubmitType::class, ['label' => 'MSC.requestPassword', 'translation_domain' => 'contao_default'])
            ;
        }

        $builder->add('request_token', ContaoRequestTokenType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'reset_password' => false,
        ]);

        $resolver->setAllowedTypes('reset_password', 'bool');
    }
}
