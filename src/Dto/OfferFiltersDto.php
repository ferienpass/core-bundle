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

namespace Ferienpass\CoreBundle\Dto;

use Ferienpass\CoreBundle\Dto\Annotation\OfferFilterType;

class OfferFiltersDto
{
    #[OfferFilterType]
    public ?string $name = null;
    #[OfferFilterType]
    public ?int $fee = null;
    #[OfferFilterType]
    public ?int $age = null;
    #[OfferFilterType(shortForm: true)]
    public ?bool $favorites = null;
    #[OfferFilterType]
    public ?\DateTimeInterface $earliest_date = null;
    #[OfferFilterType]
    public ?\DateTimeInterface $latest_date = null;

    public static function fromData(?array $data): self
    {
        $self = new self();

        $self->name = $data['name'] ?? null;
        $self->fee = $data['fee'] ?? null;
        $self->age = $data['age'] ?? null;
        $self->favorites = isset($data['favorites']) ? (bool) $data['favorites'] : null;
        $self->earliest_date = $data['earliest_date'] ?? null;
        $self->latest_date = $data['latest_date'] ?? null;

        return $self;
    }
}
