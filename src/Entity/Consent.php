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

namespace Ferienpass\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(['edition_agreement_letter' => AgreementLetterSignature::class, 'host_consent' => HostConsent::class])]
class Consent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $signedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $validUntil;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'string', length: 32)]
    private string $hash;

    public function __construct(User $user, string $hash, \DateTimeInterface $validUntil = null)
    {
        $this->signedAt = new \DateTimeImmutable();
        $this->user = $user;
        $this->hash = $hash;
        $this->validUntil = $validUntil;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSignedAt(): \DateTimeInterface
    {
        return $this->signedAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function isValid(string $hash): bool
    {
        if (null !== $this->validUntil && $this->validUntil < new \DateTimeImmutable()) {
            return false;
        }

        return hash_equals($this->hash, $hash);
    }
}
