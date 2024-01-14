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

namespace Ferienpass\CoreBundle\Filter\Type;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

interface FilterType
{
    public static function getName(): string;

    public function applyFilter(QueryBuilder $qb, FormInterface $form);

    public function getViewData(FormInterface $form): ?TranslatableInterface;
}
