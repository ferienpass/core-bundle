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

use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ContaoLoginPasswordType extends PasswordType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['full_name'] = 'password';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions(
            $resolver
                ->setDefault('label', 'MSC.password.0')
                ->setDefault('translation_domain', 'contao_default')
                ->setDefault('mapped', false)
        );
    }
}
