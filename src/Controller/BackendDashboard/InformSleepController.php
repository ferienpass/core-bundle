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

use Ferienpass\CoreBundle\Repository\EditionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InformSleepController extends AbstractDashboardWidgetController
{
    public function __construct(private EditionRepository $editionRepository)
    {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->show()) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->render('@FerienpassCore/Backend/Dashboard/sleep.html.twig');
    }

    private function show(): bool
    {
        foreach ($this->editionRepository->findWithActiveTask('show_offers') as $edition) {
            if ($edition->getActiveTasks('application_system')->isEmpty()) {
                return true;
            }
        }

        return false;
    }
}
