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

use Doctrine\ORM\EntityRepository;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BackendApplicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('offer', EntityType::class, [
                'class' => Offer::class,
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('o')->where('o.onlineApplication = 1'),
                'choice_label' => 'id',
            ])
            ->add('participant', EntityType::class, [
                'required' => false,
                'class' => Participant::class,
                'choice_label' => 'id',
            ])
            ->add('firstname', TextType::class, [
                'required' => false,
                'label' => 'Vorname',
            ])
            ->add('lastname', TextType::class, [
                'required' => false,
                'label' => 'Nachname',
            ])
            ->add('phone', TelType::class, [
                'required' => false,
                'label' => 'Telefonnummer',
            ])
            ->add('mobile', TelType::class, [
                'required' => false,
                'label' => 'Mobilnummer',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'E-Mail-Adresse',
            ])
            ->add('dateOfBirth', BirthdayType::class, [
                'label' => 'Geburtsdatum',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'placeholder' => 'tt.mm.jjjj',
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [Attendance::STATUS_CONFIRMED, Attendance::STATUS_WAITLISTED, Attendance::STATUS_WAITING],
            ])
            ->add('notify', CheckboxType::class, [
                'required' => false,
                'label' => 'Teilnehmer:in benachrichtigen',
                'help' => 'Erfordert eine E-Mail-Adresse und/oder Mobilnummer',
            ])
            ->add('requestToken', ContaoRequestTokenType::class)
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
