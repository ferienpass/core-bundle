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
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Contracts\Translation\TranslatableInterface;

interface OfferListFilterType
{
    public static function getName(): string;

    public function applyFilter(QueryBuilder $qb, FormInterface $form);

    public function typeGuess(): ?TypeGuess;

    public function getViewData(FormInterface $form): ?TranslatableInterface;
}
