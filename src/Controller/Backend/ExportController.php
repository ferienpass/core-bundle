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

namespace Ferienpass\CoreBundle\Controller\Backend;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Export\Offer\OfferExporter;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/export", name="backend_export")
 */
final class ExportController extends AbstractController
{
    public function __construct(private OfferRepository $offerRepository, private OfferExporter $exporter)
    {
    }

    public function __invoke(Request $request): Response
    {
        $types = $this->exporter->getAllNames();

        $form = $this->createFormBuilder()
            ->add('type', ChoiceType::class, [
                'label' => 'Welches Format soll exportiert werden?',
                'choices' => array_combine($types, $types),
                'layout' => 'cards',
                'data' => $types[0],
                'choice_label' => fn ($choice, $key, $value): string => sprintf('export.%s.0', $key),
                'choice_attr' => fn ($choice, $key, $value): array => ['help' => sprintf('export.%s.1', $key)],
            ])
            ->add('editions', EntityType::class, [
                'class' => Edition::class,
                'choice_label' => 'name',
                'label' => 'Ferienpässe',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('hosts', EntityType::class, [
                'class' => Host::class,
                'choice_label' => 'name',
                'required' => false,
                'label' => 'Veranstalter',
                'multiple' => true,
                'expanded' => false,
            ])
            ->add('published', CheckboxType::class, [
                'label' => 'nur veröffentlichte',
                'required' => false,
            ])
            ->add('export', SubmitType::class, ['label' => 'Export starten'])
            ->add('request_token', ContaoRequestTokenType::class)
            ->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $type = $form->get('type')->getData();
            $offers = $this->queryOffers($form);

            return $this->exportOffers($type, $offers);
        }

        return $this->renderForm('@FerienpassCore/Backend/be_export.html.twig', [
            'form' => $form,
        ]);
    }

    private function exportOffers(string $key, iterable $offers): BinaryFileResponse
    {
        return $this->file($this->exporter->getExporter($key)->generate($offers));
    }

    private function queryOffers(FormInterface $form): iterable
    {
        $qb = $this->offerRepository
            ->createQueryBuilder('offer')
            ->leftJoin('offer.dates', 'dates')
            ->orderBy('dates.begin', 'ASC')
        ;

        if ($form->get('published')->getData()) {
            $qb->andWhere('offer.published = 1');
        }

        if (($editions = $form->get('editions')->getData())
            && $editions instanceof Collection
            && $editions->count()) {
            $qb
                ->andWhere('offer.edition IN (:editions)')
                ->setParameter('editions', array_map(fn (Edition $e) => $e->getId(), $editions->toArray()), Types::SIMPLE_ARRAY)
            ;
        }

        if (($hosts = $form->get('hosts')->getData())
            && $hosts instanceof Collection
            && $hosts->count()) {
            $qb
                ->innerJoin('offer.hosts', 'hosts')
                ->andWhere('hosts.id IN (:hosts)')
                ->setParameter('hosts', array_map(fn (Host $h) => $h->getId(), $hosts->toArray()), Types::SIMPLE_ARRAY)
            ;
        }

        return $qb->getQuery()->getResult();
    }
}
