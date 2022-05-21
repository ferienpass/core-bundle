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

namespace Ferienpass\CoreBundle\Form\TypeGuesser;

use Ferienpass\CoreBundle\Dto\OfferFiltersDto;
use Ferienpass\CoreBundle\Filter\Type\OfferListFilterType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;
use Symfony\Component\Form\Guess\ValueGuess;

class OfferFiltersTypeGuesser implements FormTypeGuesserInterface
{
    /** @var array<string,OfferListFilterType> */
    private array $filterTypes = [];

    public function __construct(iterable $filterTypes)
    {
        $this->filterTypes = $filterTypes instanceof \Traversable ? iterator_to_array($filterTypes, true) : $this->filterTypes;
    }

    public function guessType(string $class, string $property): ?TypeGuess
    {
        if (!is_a($class, OfferFiltersDto::class, true)) {
            return null;
        }

        return $this->getFilterType($property)?->typeGuess();
    }

    public function guessRequired(string $class, string $property): ?ValueGuess
    {
        if (!is_a($class, OfferFiltersDto::class, true)) {
            return null;
        }

        return new ValueGuess(false, Guess::HIGH_CONFIDENCE);
    }

    public function guessMaxLength(string $class, string $property): ?ValueGuess
    {
        return null;
    }

    public function guessPattern(string $class, string $property): ?ValueGuess
    {
        return null;
    }

    private function getFilterType(string $name): ?OfferListFilterType
    {
        return $this->filterTypes[$name] ?? null;
    }
}
