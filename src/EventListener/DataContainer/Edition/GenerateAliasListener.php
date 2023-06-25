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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Edition;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\Slug\Slug;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

class GenerateAliasListener
{
    public function __construct(private Connection $connection, private Slug $slug)
    {
    }

    /**
     * @Callback(table="Edition", target="fields.alias.save")
     */
    public function onSaveCallback(?string $value, DataContainer $dc): ?string
    {
        $aliasExists = fn (string $alias): bool => false !== $this->connection->executeQuery(
            'SELECT id FROM Edition WHERE alias=:alias AND id!=:id', [
                'alias' => $alias,
                'id' => $dc->id,
            ])->fetchOne();

        // Generate an alias if there is none
        if (!$value && $dc->activeRecord->name) {
            $value = $this->slug->generate($dc->activeRecord->name, ['delimiter' => ''], $aliasExists);
        } elseif ($aliasExists($value)) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $value));
        }

        return $value;
    }
}
