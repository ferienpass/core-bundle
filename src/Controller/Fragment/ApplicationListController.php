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

namespace Ferienpass\CoreBundle\Controller\Fragment;

use Contao\FrontendUser;
use Doctrine\Common\Collections\Collection;
use Ferienpass\CoreBundle\ApplicationSystem\ApplicationSystems;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Facade\AttendanceFacade;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Ferienpass\CoreBundle\Repository\AttendanceRepository;
use Ferienpass\CoreBundle\Ux\Flash;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationListController extends AbstractController
{
    private ApplicationSystems $applicationSystems;
    private AttendanceFacade $attendanceFacade;
    private AttendanceRepository $attendanceRepository;

    public function __construct(ApplicationSystems $applicationSystems, AttendanceFacade $attendanceFacade, AttendanceRepository $attendanceRepository)
    {
        $this->applicationSystems = $applicationSystems;
        $this->attendanceFacade = $attendanceFacade;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof FrontendUser) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        /** @var Collection|Attendance[] $attendances */
        $attendances = $this->attendanceRepository->createQueryBuilder('a')
            ->innerJoin('a.participant', 'p')
            ->where('p.member = :member')
            ->setParameter('member', $user->id)
            ->getQuery()
            ->getResult()
        ;

        $forms = iterator_to_array($this->withdrawForms($attendances), true);
        foreach ($forms as $form) {
            if ($response = $this->handleWithdraw($form, $request)) {
                return $response;
            }
        }

        $applicationSystems = [];
        foreach ($attendances as $attendance) {
            $applicationSystems[$attendance->getId()] = $this->applicationSystems->findApplicationSystem($attendance->getOffer());
        }

        // ICS link
//        $member = FrontendUser::getInstance();
//        $token = hash('ripemd128', implode('', [$member->id, 'ics', $this->secret]));
//        $token = substr($token, 0, 8);
//
//        return $base.'/share/anmeldungen-ferienpass-'.$member->id.'-'.$token.'.ics';

        return $this->render('@FerienpassCore/Fragment/application_list.html.twig', [
            'attendances' => $attendances,
            'withdraw' => array_map(fn (FormInterface $form) => $form->createView(), $forms),
            'applicationSystems' => $applicationSystems,
        ]);
    }

    /**
     * @param iterable<int, Attendance> $attendances
     */
    private function withdrawForms(iterable $attendances): \Generator
    {
        $now = new \DateTimeImmutable();

        foreach ($attendances as $attendance) {
            if ($attendance->isWithdrawn()) {
                continue;
            }

            $dates = $attendance->getOffer()->getDates();
            if ($dates->isEmpty() || $now > $dates->first()->getBegin()) {
                continue;
            }

            $deadline = $attendance->getOffer()->getApplicationDeadline();
            if ($deadline && $now >= $deadline) {
                continue;
            }

            yield $attendance->getId() => $this->get('form.factory')->createNamed((string) $attendance->getId())
                ->add('submit', SubmitType::class, ['label' => 'Abmelden'])
                ->add('requestToken', ContaoRequestTokenType::class)
            ;
        }
    }

    private function handleWithdraw(FormInterface $form, Request $request): ?Response
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return null;
        }

        $attendance = $this->attendanceRepository->find($form->getConfig()->getName());
        if (!$attendance instanceof Attendance) {
            return $this->redirectToRoute($request->get('_route'));
        }

        $applicationSystem = $this->applicationSystems->findApplicationSystem($attendance->getOffer());
        if (null === $applicationSystem) {
            $this->addFlash('error', 'Zurzeit sind keine Anmeldungen möglich');

            return $this->redirectToRoute($request->get('_route'));
        }

        $this->denyAccessUnlessGranted('withdraw', $attendance);

        $this->attendanceFacade->delete($attendance);

        $this->addFlash(...Flash::confirmation()->text('Die Anmeldung wurde erfolgreich zurückgezogen')->create());

        return $this->redirectToRoute($request->get('_route'));
    }
}
