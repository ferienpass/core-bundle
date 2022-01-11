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

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\FilesModel;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\OfferMemberAssociation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class OfferDetailsController extends AbstractController
{
    public function __construct(private string $projectDir)
    {
    }

    public function __invoke(Offer $offer, Request $request): Response
    {
        $members = $offer->getMemberAssociations()->map(fn (OfferMemberAssociation $a) => MemberModel::findByPk($a->getMember()));

        if ($request->query->has('file')) {
            $this->handleDownload($request, $offer);
        }

        return $this->render('@FerienpassCore/Fragment/offer_details.html.twig', [
            'offer' => $offer,
            'members' => $members,
        ]);
    }

    private function handleDownload(Request $request, Offer $offer): void
    {
        $files = [];
        if ($offer->getAgreementLetter()) {
            $files[] = $offer->getAgreementLetter();
        }

        $download = $request->query->get('file');
        $file = FilesModel::findByPath($download);
        if (null === $file || !\in_array($file->uuid, $files, true)) {
            throw new PageNotFoundException('Invalid download');
        }

        // Cannot return response because it
        // cannot be handled through fragment renderer.
        throw new ResponseException($this->file($this->projectDir.'/'.$file->path));
    }
}
