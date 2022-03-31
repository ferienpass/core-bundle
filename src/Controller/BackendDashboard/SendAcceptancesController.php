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

use Ferienpass\CoreBundle\Applications\UnconfirmedApplications;
use Symfony\Component\HttpFoundation\Response;

class SendAcceptancesController extends AbstractDashboardWidgetController
{
    public function __construct(private UnconfirmedApplications $unconfirmedApplications)
    {
    }

    public function __invoke(): Response
    {
        // TODO: Only show widget when there is a current Edition with active/finished "allocation" task
        // TODO 2: Make the "allocation" task mandatory for Editions with lot application system
        $count = \count($this->unconfirmedApplications->getUninformedMembers()) + \count($this->unconfirmedApplications->getUninformedParticipants());
        if (!$count) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        return $this->render('@FerienpassCore/Backend/Dashboard/send_acceptances.html.twig', [
            'count' => $count,
        ]);
    }
}
