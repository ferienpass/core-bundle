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

namespace Ferienpass\CoreBundle\Form\CompundType;

use Contao\MemberModel;
use Ferienpass\CoreBundle\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'Vorname',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Nachname',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('dateOfBirth', BirthdayType::class, [
                'label' => 'Geburtsdatum',
                'help' => 'Einige Angebote erfordern ein Alter.',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'placeholder' => 'tt.mm.jjjj',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'empty_data' => function (FormInterface $form) {
                $member = $form->getConfig()->getOption('member');
                if ($member instanceof MemberModel) {
                    return new Participant((int) $member->id);
                }

                return new Participant();
            },
        ]);

        $resolver->setDefined('member');
        $resolver->setAllowedTypes('member', MemberModel::class);
    }
}
