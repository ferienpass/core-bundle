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

use Contao\CoreBundle\Controller\AbstractBackendController;
use Ferienpass\CoreBundle\Facade\EraseDataFacade;
use Ferienpass\CoreBundle\Form\SimpleType\ContaoRequestTokenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/daten-löschen', name: 'backend_erase_data')]
final class EraseDataController extends AbstractBackendController
{
    public function __construct(private EraseDataFacade $eraseDataFacade)
    {
    }

    public function __invoke(Request $request): Response
    {
        $this->initializeContaoFramework();

        $participants = $this->eraseDataFacade->expiredParticipants();
        if (empty($participants)) {
            return $this->redirectToRoute('contao_backend');
        }

        $form = $this->createFormBuilder()->add('requestToken', ContaoRequestTokenType::class)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->eraseDataFacade->eraseData();

            return $this->redirectToRoute('contao_backend');
        }

        return $this->renderForm('@FerienpassCore/Backend/erase-data.html.twig', [
            'participants' => $participants,
            'form' => $form,
        ]);
    }
}
