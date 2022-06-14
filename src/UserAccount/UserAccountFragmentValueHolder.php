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

namespace Ferienpass\CoreBundle\UserAccount;

class UserAccountFragmentValueHolder
{
    public function __construct(private string $key, private string $alias, private string $icon)
    {
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }
}
