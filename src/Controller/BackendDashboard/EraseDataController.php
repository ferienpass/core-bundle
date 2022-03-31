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

namespace Ferienpass\CoreBundle\Controller\BackendDashboard;

use Ferienpass\CoreBundle\Facade\EraseDataFacade;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EraseDataController extends AbstractDashboardWidgetController
{
    public function __construct(private EraseDataFacade $eraseDataFacade, private EditionRepository $editionRepository)
    {
    }

    public function __invoke(Request $request): Response
    {
        if (0 !== \count($this->editionRepository->findWithActiveTask('application_system'))) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $participants = $this->eraseDataFacade->expiredParticipants();
        if (0 === \count($participants)) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->render('@FerienpassCore/Backend/Dashboard/erase_data.html.twig', [
            'participants' => $participants,
        ]);
    }
}
