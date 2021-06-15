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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Offer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

class AliasListener
{
    private Slug $slug;
    private Connection $connection;

    public function __construct(Slug $slug, Connection $connection)
    {
        $this->slug = $slug;
        $this->connection = $connection;
    }

    /**
     * @Callback(table="Offer", target="config.onsubmit")
     */
    public function saveAlias(DataContainer $dc): void
    {
        $alias = $dc->id.'-'.$this->slug->generate($dc->activeRecord->name);

        $this->connection->executeQuery('UPDATE Offer SET alias=:alias WHERE id=:id', [
            'alias' => $alias,
            'id' => $dc->id,
        ]);
    }
}
