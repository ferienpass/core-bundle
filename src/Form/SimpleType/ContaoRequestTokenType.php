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

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class ContaoRequestTokenType extends HiddenType
{
    public function __construct(private readonly CsrfTokenManagerInterface $tokenManager, #[Autowire(param: 'contao.csrf_token_name')] private readonly string $tokenName)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['full_name'] = 'REQUEST_TOKEN';
        $view->vars['value'] = $this->tokenManager->getToken($this->tokenName)->getValue();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions(
            $resolver
                ->setDefault('mapped', false)
        );
    }
}
