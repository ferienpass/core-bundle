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

namespace Ferienpass\CoreBundle\Payments\Provider;

use Ferienpass\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

interface PaymentProviderInterface
{
    /**
     * @return string the redirect URL
     */
    public function initializePayment(array $attendances, User $user): string;

    public function isRedirectSuccessful(Request $request): bool;
}
