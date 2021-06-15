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

use Contao\FrontendUser;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\ApplicationSystem\FirstComeApplicationSystem;
use Ferienpass\CoreBundle\ApplicationSystem\TimedApplicationSystemInterface;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\OfferDate;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Exception\IneligibleParticipantException;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Translation\TranslatableMessage;

class ApplyFormType extends AbstractType
{
    private Security $security;
    private ManagerRegistry $doctrine;

    public function __construct(Security $security, ManagerRegistry $doctrine)
    {
        $this->security = $security;
        $this->doctrine = $doctrine;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();
        if (!$user instanceof FrontendUser) {
            return;
        }

        $offer = $options['offer'];
        $applicationSystem = $options['application_system'];
        \assert($offer instanceof Offer);
        \assert($applicationSystem instanceof TimedApplicationSystemInterface);

        $builder
            ->add('participants', EntityType::class, $this->getChoiceOptions($offer, $applicationSystem))
            ->add('request_token', ContaoRequestTokenType::class)
            ->add('submit', SubmitType::class, ['label' => 'Zum Angebot anmelden'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);

        $resolver->setDefined('offer');
        $resolver->setDefined('application_system');
        $resolver->setAllowedTypes('offer', Offer::class);
        $resolver->setAllowedTypes('application_system', TimedApplicationSystemInterface::class);
    }

    private function getChoiceOptions(Offer $offer, TimedApplicationSystemInterface $applicationSystem): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof FrontendUser) {
            return [];
        }

        return [
            'class' => Participant::class,
            'query_builder' => fn (EntityRepository $er) => $er
                ->createQueryBuilder('p')
                ->andWhere('p.member = :member')
                ->setParameter('member', $user->id),
            'multiple' => true,
            'expanded' => true,
            'choice_label' => fn (Participant $choice) => sprintf('%s %s', $choice->getFirstname(), $choice->getLastname()),
            'choice_attr' => function (Participant $key, $val, $index) use ($offer, $applicationSystem): array {
                if ($this->participantIsApplied($key, $offer)) {
                    return ['disabled' => 'disabled', 'selected' => 'true'];
                }

                try {
                    $this->ineligibility($offer, $key, $applicationSystem);
                } catch (IneligibleParticipantException $e) {
                    return [
                        'message' => $e->getUserMessage(),
                        'disabled' => 'disabled',
                    ];
                }

                return [];
            },
        ];
    }

    private function participantIsApplied(Participant $participant, Offer $offer): bool
    {
        return $participant->getAttendances()->filter(fn (Attendance $a) => $offer === $a->getOffer())->count() > 0;
    }

    private function ineligibility(Offer $offer, Participant $participant, TimedApplicationSystemInterface $applicationSystem): void
    {
        $this->ageValid($offer, $participant, $applicationSystem);
        $this->overlappingOffer($participant, $offer);
        $this->limitReached($offer, $participant, $applicationSystem);
        $this->dayLimitReached($offer, $participant, $applicationSystem);
    }

    private function ageValid(Offer $offer, Participant $participant, TimedApplicationSystemInterface $applicationSystem): void
    {
        if (!$offer->getMinAge() && !$offer->getMaxAge()) {
            return;
        }

        /** @var OfferDate $date */
        $date = $offer->getDates()->first();
        if (null === $date || null === $dateBegin = $date->getBegin()) {
            return;
        }

        $dateOfBirth = $participant->getDateOfBirth();
        if (null === $dateOfBirth) {
            return;
        }

        $ageCheck = $applicationSystem->getTask()->getAgeCheck();
        if ('vague_on_year' === $ageCheck) {
            $ages = [
                $dateOfBirth->diff((new \DateTimeImmutable($dateBegin->format('Y').'-01-01')))->y,
                $dateOfBirth->diff((new \DateTimeImmutable($dateBegin->format('Y').'-12-31')))->y,
            ];

            if (($offer->getMinAge() && max($ages) < $offer->getMinAge()) || ($offer->getMaxAge() && min($ages) > $offer->getMaxAge())) {
                throw new IneligibleParticipantException($offer, $participant, new TranslatableMessage('ineligible.invalidAge', ['name' => $participant->getFirstname()]));
            }
        }

        $age = $participant->getAge($dateBegin);

        if (($offer->getMinAge() && $age < $offer->getMinAge()) || ($offer->getMaxAge() && $age > $offer->getMaxAge())) {
            throw new IneligibleParticipantException($offer, $participant, new TranslatableMessage('ineligible.invalidAge', ['name' => $participant->getFirstname()]));
        }
    }

    private function overlappingOffer(Participant $participant, Offer $offer): void
    {
        /** @var EntityRepository $repo */
        $repo = $this->doctrine->getRepository(OfferDate::class);

        // All dates of offers the participant is participating (expect current offer)
        /** @var iterable<int, OfferDate> $participatingDates */
        $participatingDates = $repo->createQueryBuilder('d')
            ->innerJoin(Offer::class, 'o', Join::WITH, 'o.id = d.offer')
            ->innerJoin(Attendance::class, 'a', Join::WITH, 'a.offer = o.id')
            ->where('a.participant = :participant')
            ->andWhere('a.offer <> :offer')
            ->setParameter('participant', $participant, Types::INTEGER)
            ->setParameter('offer', $offer, Types::INTEGER)
            ->getQuery()
            ->getResult()
        ;

        // Walk every date the participant is already attending to…
        foreach ($offer->getDates() as $currentDate) {
            foreach ($participatingDates as $participatingDate) {
                // …check for an overlap
                if (($participatingDate->getEnd() >= $currentDate->getBegin()) && ($currentDate->getEnd() >= $participatingDate->getBegin())) {
                    throw new IneligibleParticipantException($offer, $participant, new TranslatableMessage('ineligible.overlap', ['name' => $participant->getFirstname(), 'offer' => $participatingDate->getOffer()->getName()]));
                }
            }
        }
    }

    private function limitReached(Offer $offer, Participant $participant, TimedApplicationSystemInterface $applicationSystem): void
    {
        $task = $applicationSystem->getTask();
        if (!$task->getMaxApplications()) {
            return;
        }

        $attendances = $participant
            ->getAttendancesNotWithdrawn()
            ->filter(fn (Attendance $a) => $a->getTask() && $a->getTask()->getId() === $task->getId())
        ;

        if (\count($attendances) >= $task->getMaxApplications()) {
            throw new IneligibleParticipantException($offer, $participant, new TranslatableMessage('ineligible.limitReached', ['name' => $participant->getFirstname(), 'max' => $task->getMaxApplications()]));
        }
    }

    private function dayLimitReached(Offer $offer, Participant $participant, TimedApplicationSystemInterface $applicationSystem): void
    {
        if (!$applicationSystem instanceof FirstComeApplicationSystem) {
            return;
        }

        $task = $applicationSystem->getTask();
        $limit = $task->getMaxApplicationsDay();
        if (!$limit) {
            return;
        }

        $today = new \DateTimeImmutable();
        $dayBegin = new \DateTimeImmutable($today->format('Y-m-d 00:00'));
        $dayEnd = new \DateTimeImmutable($today->format('Y-m-d 23:59:59'));

        $attendances = $participant
            ->getAttendances()
            ->filter(fn (Attendance $a) => $a->getTask() && $a->getTask()->getId() === $task->getId() && $a->getCreatedAt() >= $dayBegin && $a->getCreatedAt() <= $dayEnd)
        ;

        if (\count($attendances) >= $limit) {
            throw new IneligibleParticipantException($offer, $participant, new TranslatableMessage('ineligible.dayLimitReached', ['name' => $participant->getFirstname(), 'max' => $task->getMaxApplications()]));
        }
    }
}
