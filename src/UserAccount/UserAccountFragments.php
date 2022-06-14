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

class UserAccountFragments
{
    /**
     * @var array<string,UserAccountFragmentValueHolder>
     */
    private array $fragments;

    public function __construct()
    {
        $this->fragments = [];
    }

    public function addFragment(string $key, string $alias, string $icon): void
    {
        $this->fragments[$key] = new UserAccountFragmentValueHolder($key, $alias, $icon);
    }

    public function all(): array
    {
        return $this->fragments;
    }

    public function keys(): array
    {
        return array_keys($this->fragments);
    }
}
