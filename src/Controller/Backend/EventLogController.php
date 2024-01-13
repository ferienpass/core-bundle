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
use Doctrine\Common\Collections\Criteria;
use Ferienpass\CoreBundle\Message\ParticipantListChanged;
use Ferienpass\CoreBundle\Repository\EventLogRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/ereignisse', name: 'backend_event_log')]
final class EventLogController extends AbstractBackendController
{
    public function __construct(private readonly EventLogRepository $eventLogRepository)
    {
    }

    public function __invoke(Request $request): Response
    {
        $criteria = (new Criteria())
            ->where(Criteria::expr()->neq('message', ParticipantListChanged::class))
            ->orderBy(['createdAt' => 'DESC'])
        ;

        $this->initializeContaoFramework();

        $events = $this->eventLogRepository->matching($criteria);

        return $this->render('@FerienpassCore/Backend/event-log.html.twig', [
            'events' => $events,
        ]);
    }
}
