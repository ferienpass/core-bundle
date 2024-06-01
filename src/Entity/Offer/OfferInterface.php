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

namespace Ferienpass\CoreBundle\Entity\Offer;

use Doctrine\Common\Collections\Collection;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Entity\Participant\ParticipantInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

interface OfferInterface
{
    final public const STATE_DRAFT = 'draft';
    final public const STATE_COMPLETED = 'completed';
    final public const STATE_REVIEWED = 'reviewed';
    final public const STATE_PUBLISHED = 'published';
    final public const STATE_UNPUBLISHED = 'unpublished';
    final public const STATE_CANCELLED = 'cancelled';
    final public const TRANSITION_COMPLETE = 'complete';
    final public const TRANSITION_APPROVE = 'approve';
    final public const TRANSITION_UNAPPROVE = 'unapprove';
    final public const TRANSITION_PUBLISH = 'publish';
    final public const TRANSITION_UNPUBLISH = 'unpublish';
    final public const TRANSITION_CANCEL = 'cancel';
    final public const TRANSITION_RELAUNCH = 'relaunch';

    public function getId(): ?int;

    public function getName(): string;

    public function getAlias(): ?string;

    public function isVariantBase(): bool;

    public function hasVariants(): bool;

    public function isVariant(): bool;

    public function getVariants(): Collection;

    public function getVariantBase(): ?self;

    public function getHosts(): Collection;

    // TODO refactor to own interface, make non-nullable
    public function getEdition(): ?Edition;

    // TODO refactor to own interface, make non-nullable
    public function getFee(): ?int;

    public function getDates(): Collection;

    public function isPublished(): bool;

    public function requiresApplication(): bool;

    public function isOnlineApplication(): bool;

    public function getAttendances(): Collection;

    public function addAttendance(Attendance $attendance): void;

    public function generateAlias(SluggerInterface $slugger): void;

    public function getFeePayable(ParticipantInterface $participant, EventDispatcherInterface $dispatcher): int;
}
