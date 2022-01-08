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

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Response;

class EditionMenuController extends AbstractDashboardWidgetController
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(): Response
    {
        $editions = $this->connection->createQueryBuilder()
            ->select('e.id', 'e.name', 'COUNT(o.id) as count')
            ->from('Edition', 'e')
            ->innerJoin('e', 'Offer', 'o', 'o.edition = e.id')
            ->groupBy('o.edition')
            ->orderBy('e.tstamp', 'DESC')
            ->setMaxResults(4)
            ->executeQuery()
            ->fetchAllAssociative()
        ;

        return $this->render('@FerienpassCore/Backend/Dashboard/edition_menu.html.twig', [
            'editions' => $editions,
        ]);
    }
}
