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

namespace Ferienpass\CoreBundle\Filter;

use Doctrine\ORM\QueryBuilder as DoctrineQueryBuilder;
use Ferienpass\CoreBundle\Filter\Type\FilterType;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

class OfferListFilter
{
    /** @var array<string, TranslatableInterface> */
    private array $filtersViewData = [];

    /**
     * @param array<string,FilterType> $filterTypes
     */
    public function __construct(private readonly FormInterface $form, private readonly DoctrineQueryBuilder $queryBuilder, private array $filterTypes)
    {
    }

    public function applyFilter(array $values): self
    {
        // Re-evaluate the form by the actual values from the URL
        $this->form->submit($values);

        foreach (array_keys((array) $this->form->getData()) as $name) {
            $form = $this->form->get($name);
            if ($form->isEmpty()) {
                continue;
            }

            $this->getFilterType($name)?->applyFilter($this->queryBuilder, $form);

            $this->filtersViewData[$name] = $this->getFilterType($name)?->getViewData($form);
        }

        return $this;
    }

    /**
     * Used in the template to retrieve human-readable versions of the applied filters.
     *
     * @return array<string, TranslatableInterface>
     */
    public function humanReadable(): array
    {
        return $this->filtersViewData;
    }

    private function getFilterType(string $name): ?FilterType
    {
        return $this->filterTypes[$name] ?? null;
    }
}
