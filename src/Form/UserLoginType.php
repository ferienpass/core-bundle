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

use Ferienpass\CoreBundle\Form\SimpleType\ContaoLoginFormSubmitType;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoLoginPasswordType;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoLoginUsernameType;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoTargetPathType;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoUseTargetPathType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', ContaoLoginUsernameType::class)
            ->add('password', ContaoLoginPasswordType::class)
            ->add('autologin', CheckboxType::class, [
                'required' => false,
                'label' => 'MSC.autologin',
                'translation_domain' => 'contao_default',
            ])
            ->add('FORM_SUBMIT', ContaoLoginFormSubmitType::class)
            ->add('_target_path', ContaoTargetPathType::class, ['data' => $options['target_path']])
            ->add('_always_use_target_path', ContaoUseTargetPathType::class)
            ->add('request_token', ContaoRequestTokenType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'MSC.login',
                'translation_domain' => 'contao_default',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'target_path' => '',
        ]);

        $resolver
            ->setAllowedTypes('target_path', 'string')
            ->setRequired('target_path')
        ;
    }
}
