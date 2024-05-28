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

namespace Ferienpass\CoreBundle\Cron;

use Contao\CoreBundle\Cron\Cron;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Ferienpass\CoreBundle\Repository\PaymentRepository;

#[AsCronJob('daily')]
class TruncatePayments
{
    public function __construct(private readonly PaymentRepository $payments)
    {
    }

    public function __invoke(string $scope): void
    {
        if (Cron::SCOPE_WEB === $scope) {
            return;
        }

        $this->payments->createQueryBuilder('payment')
            ->delete()
            ->where('payment.status IS NULL')
            ->andWhere('payment.createdAt <= :date')
            ->setParameter('date', new \DateTimeImmutable('-30 days'))
            ->andWhere()
        ;
    }
}
