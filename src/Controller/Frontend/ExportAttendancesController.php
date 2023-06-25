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

namespace Ferienpass\CoreBundle\Controller\Frontend;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Export\Offer\ICal\ICalExport;
use Ferienpass\CoreBundle\Repository\OfferRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

#[Route(defaults: ['token_check' => false])]
class ExportAttendancesController extends \Contao\CoreBundle\Controller\AbstractController
{
    public function __construct(private string $secret, private ICalExport $iCal, private OfferRepository $offerRepository)
    {
    }

    #[Route(path: '/share/anmeldungen-ferienpass-{memberId}-{token}.{_format}', defaults: ['format' => 'ics'], requirements: ['memberId' => '\d+'])]
    public function __invoke(int $memberId, string $token, string $_format, Request $request)
    {
        if (null === $member = MemberModel::findByPk($memberId)) {
            throw new PageNotFoundException('Member ID not found: '.$memberId);
        }

        $expectedToken = hash('ripemd128', implode('', [$member->id, $_format, $this->secret]));
        $expectedToken = substr($expectedToken, 0, 8);
        if (false === hash_equals($expectedToken, $token)) {
            throw new BadCredentialsException();
        }

        if ('ics' !== $_format) {
            throw new PageNotFoundException('Format not supported: '.$_format);
        }

        $offers = $this->offerRepository->createQueryBuilder('o')
            ->innerJoin(Attendance::class, 'a')
            ->innerJoin(Participant::class, 'p')
            ->where('p.member = :member')
            ->setParameter('member', $memberId)
            ->getQuery()
            ->getResult()
        ;

        $response = new BinaryFileResponse($this->iCal->generate($offers));
        $response->headers->set('Content-Type', 'text/calendar');

        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'cal.ics');

        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
