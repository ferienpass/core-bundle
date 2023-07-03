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

namespace Ferienpass\CoreBundle\Payments;

use Ferienpass\CoreBundle\Repository\PaymentRepository;

class ReceiptNumberGenerator
{
    private int $length = 0;

    public function __construct(private string $prefix, private PaymentRepository $paymentRepository)
    {
    }

    public function generate(): string
    {
        $offset1 = mb_strlen($this->prefix);

        $qb = $this->paymentRepository->createQueryBuilder('p');

        $select = 'p.receiptNumber';
        if ($offset1) {
            $select = sprintf('substring(%s, %s)', $select, ++$offset1);
        }

        // Easy way to "cast" to integer (substring returns string, no natural sorting)
        // when CAST() function is not available in ORM.
        $select = "$select + 0";

        $qb->select("$select AS docNumber");

        if ($this->prefix) {
            $qb->where($qb->expr()->like('p.receiptNumber', "'$this->prefix%'"));
        }

        $qb->orderBy('docNumber', 'DESC');

        $result = $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
        $docNumber = null === $result ? 0 : ((int) ($result['docNumber'] ?? 0));

        return sprintf('%s%s', $this->prefix, str_pad((string) ++$docNumber, $this->length, '0', \STR_PAD_LEFT));
    }
}
