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

use Contao\CoreBundle\Controller\AbstractController;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\ParticipantList\PdfExport;
use Ferienpass\CoreBundle\Export\ParticipantList\WordExport;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/angebot/{id}/anmeldungen", requirements={"id"="\d+"})
 */
class OfferApplicationsController extends AbstractController
{
    private Connection $connection;
    private PdfExport  $pdfExport;
    private WordExport $wordExport;

    public function __construct(Connection $connection, PdfExport $pdfExport, WordExport $wordExport)
    {
        $this->connection = $connection;
        $this->pdfExport = $pdfExport;
        $this->wordExport = $wordExport;
    }

    /**
     * @Route("", name="backend_offer_applications")
     */
    public function __invoke(Offer $offer, Request $request, Session $session, ManagerRegistry $doctrine): Response
    {
        if ($request->isMethod('POST') && 'confirm_all_waiting' === $request->request->get('FORM_SUBMIT')) {
            $attendances = $offer->getAttendancesWaiting();

            /** @var Attendance|false $lastAttendance */
            $lastAttendance = $offer->getAttendancesConfirmed()->last();
            $sorting = $lastAttendance ? $lastAttendance->getSorting() : 0;

            foreach ($attendances as $a) {
                $a->setConfirmed();
                $a->setSorting($sorting += 128);
            }

            $doctrine->getManager()->flush();

            return $this->redirect($request->getRequestUri());
        }

        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = $session->getBag('contao_backend');
        $autoAssign = $sessionBag->get('autoAssign', false);

        $toggleMode = $this->createFormBuilder(['auto' => $autoAssign])
            ->add('auto', CheckboxType::class, ['false_values' => ['']])
            ->add('REQUEST_TOKEN', ContaoRequestTokenType::class)
            ->getForm()
        ;

        $toggleMode->handleRequest($request);
        if ($toggleMode->isSubmitted() && $toggleMode->isValid()) {
            $autoAssign = $toggleMode->get('auto')->getData();

            $sessionBag->set('autoAssign', $autoAssign);
        }

        $statement = $this->connection->executeQuery(
            <<<'SQL'
SELECT DISTINCT a.id as attendanceId,
       p.id,
       p.firstname,
       p.lastname,
       p.member_id as parentId,
       IFNULL(p.email, m.email) as email,
       a.createdAt as enrolled_at,
       a.status,
       a.sorting,
       TIMESTAMPDIFF(YEAR, p.dateOfBirth, d.begin) AS age,
       (select count(*) from Attendance where participant_id = a.participant_id) AS count
FROM Attendance a
         INNER JOIN Participant p on p.id = a.participant_id
         INNER JOIN Offer f on f.id = a.offer_id
         LEFT JOIN tl_member m on m.id = p.member_id
         INNER JOIN OfferDate d on d.offer_id = f.id
WHERE a.offer_id = :offer
ORDER BY a.status, a.sorting
SQL
            ,
            ['offer' => $offer->getId()]
        );

        $emails = [];
        $attendances = [];
        foreach ($statement->fetchAllAssociative() as $attendance) {
            $attendances[$attendance['status']][] = $attendance;
            $emails[] = $attendance['email'];
        }

        return $this->render('@FerienpassCore/Backend/offer-applications.html.twig', [
            'offer' => $offer,
            'toggleMode' => $toggleMode->createView(),
            'attendances' => $attendances,
            'emails' => array_unique($emails),
        ]);
    }

    /**
     * @Route(".pdf", name="backend_offer_applications_pdf")
     */
    public function pdf(Offer $offer): Response
    {
        $path = $this->pdfExport->generate($offer);

        return $this->file($path, 'teilnahmeliste.pdf');
    }

    /**
     * @Route(".docx", name="backend_offer_applications_docx")
     */
    public function docx(Offer $offer): Response
    {
        $path = $this->wordExport->generate($offer);

        return $this->file($path, 'teilnahmeliste.docx');
    }
}
