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

class CreateSeasonController extends AbstractDashboardWidgetController
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(): Response
    {
        $hasEdition = 0 !== $this->connection
                ->createQueryBuilder()
                ->select('DISTINCT e.id')
                ->from('Edition', 'e')
                ->innerJoin('e', 'EditionTask', 't', 't.pid = e.id')
                ->where('t.periodEnd > NOW()')
                ->executeQuery()
                ->rowCount()
        ;

        if ($hasEdition) {
            return new Response();
        }

        return $this->render('@FerienpassCore/Backend/Dashboard/create_season.html.twig');
    }
}
