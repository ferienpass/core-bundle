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
    public const int REDIRECT_USER_CANCELLED = -1;
    public const int REDIRECT_PAYMENT_FAILED = 0;
    public const int REDIRECT_PAYMENT_VALIDATE = 1;
    public const int REDIRECT_PAYMENT_ON_HOLD = 2;
    public const int REDIRECT_UNKNOWN_ERROR = 9;

    /**
     * @return string the redirect URL
     */
    public function initializePayment(array $attendances, User $user): string;

    public function interpretRedirect(Request $request): int;
}
