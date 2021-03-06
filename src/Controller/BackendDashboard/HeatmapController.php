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

class HeatmapController extends AbstractDashboardWidgetController
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(): Response
    {
        return $this->render('@FerienpassCore/Backend/Dashboard/attendances_heatmap.html.twig', [
            'values' => $this->countAttendancesByDay(),
        ]);
    }

    private function countOffersByDay(): array
    {
        $days = $this->connection
            ->query(
                <<<'SQL'
SELECT DATE_FORMAT(d.begin, '%Y-%m-%d') AS day, COUNT(*)
FROM OfferDate d
WHERE d.begin > (NOW() - INTERVAL 1 YEAR)
GROUP BY day
ORDER BY day
SQL
                ,
            )
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $return = [];
        foreach ($days as $Ymd => $count) {
            $return[strtotime($Ymd)] = (int) $count;
        }

        return $return;
    }

    private function countAttendancesByDay(): array
    {
        $days = $this->connection
            ->query(
                <<<'SQL'
SELECT DATE_FORMAT(a.createdAt, '%Y-%m-%d') AS day, COUNT(*)
FROM Attendance a
WHERE a.createdAt > (NOW() - INTERVAL 1 YEAR)
GROUP BY day
ORDER BY day
SQL
            )
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $return = [];
        foreach ($days as $Ymd => $count) {
            $return[strtotime($Ymd)] = (int) $count;
        }

        return $return;
    }
}
