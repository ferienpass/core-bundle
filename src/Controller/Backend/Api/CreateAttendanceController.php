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

namespace Ferienpass\CoreBundle\Controller\Backend\Api;

use Contao\StringUtil;
use Doctrine\ORM\Query\Expr\Join;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\OfferDate;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Facade\AttendanceFacade;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Ferienpass\CoreBundle\Repository\ParticipantRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/create_attendance')]
#[IsGranted('ROLE_ADMIN')]
final class CreateAttendanceController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(private readonly OfferRepository $offerRepository, private readonly ParticipantRepository $participantRepository, private readonly AttendanceFacade $attendanceFacade)
    {
    }

    #[Route(path: '/offers', methods: ['GET'])]
    public function offers(Request $request): JsonResponse
    {
        $qb = $this->offerRepository->createQueryBuilder('o');

        $i = 0;
        foreach (StringUtil::trimsplit(' ', $request->query->get('q', '')) as $q) {
            $qb
                ->andWhere($qb->expr()->orX('o.id LIKE :q'.$i, 'o.name LIKE :q'.$i))
                ->setParameter('q'.$i++, '%'.addcslashes((string) $q, '%_').'%')
            ;
        }

        $qb
            ->andWhere('o.onlineApplication = 1')
            ->andWhere('o.cancelled <> 1')
            ->leftJoin(OfferDate::class, 'd', Join::WITH, 'd.offer = o.id')
            ->andWhere($qb->expr()->orX('d.id IS NULL', 'd.begin > CURRENT_TIMESTAMP()'))
            ->setMaxResults(50)
        ;

        $offers = $qb->getQuery()->getResult();

        $data = array_map(fn (Offer $o) => [
            'id' => $o->getId(),
            'name' => $o->getName(),
            'hosts' => implode(', ', $o->getHosts()->map(fn (Host $h) => $h->getName())->toArray()),
            'date' => $o->getDates()->count() ? $o->getDates()->first()->getBegin()->format('d.m.Y H:i') : null,
            'minParticipants' => $o->getMinParticipants() ?: null,
            'maxParticipants' => $o->getMaxParticipants() ?: null,
            'minAge' => $o->getMinAge() ?: null,
            'maxAge' => $o->getMaxAge() ?: null,
            'current' => $o->getAttendancesNotWithdrawn()->count(),
            'confirmed' => $o->getAttendancesConfirmed()->count(),
            'waitlisted' => $o->getAttendancesWithStatus('waitlisted')->count(),
            'waiting' => $o->getAttendancesWaiting()->count(),
        ], $offers);

        return new JsonResponse($data);
    }

    #[Route(path: '/participants', methods: ['GET'])]
    public function participants(Request $request): JsonResponse
    {
        $qb = $this->participantRepository->createQueryBuilder('p');

        $i = 0;
        foreach (StringUtil::trimsplit(' ', $request->query->get('q', '')) as $q) {
            $qb
                ->andWhere($qb->expr()->orX('p.id LIKE :q'.$i, 'p.firstname LIKE :q'.$i, 'p.lastname LIKE :q'.$i))
                ->setParameter('q'.$i++, '%'.addcslashes((string) $q, '%_').'%')
            ;
        }

        $qb
            ->setMaxResults(50)
        ;

        $participants = $qb->getQuery()->getResult();

        $data = array_map(fn (Participant $p) => [
            'id' => $p->getId(),
            'firstname' => $p->getFirstname(),
            'lastname' => $p->getLastname(),
            'dateOfBirth' => $p->getDateOfBirth() ? $p->getDateOfBirth()->format('d.m.Y') : null,
            'age' => $p->getAge(),
        ], $participants);

        return new JsonResponse($data);
    }

    #[Route(path: '/status/{id}', methods: ['GET'])]
    public function status(Offer $offer): JsonResponse
    {
        $attendance = $this->attendanceFacade->preview($offer, new Participant());

        return new JsonResponse(['status' => $attendance->getStatus()]);
    }
}
