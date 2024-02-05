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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Ferienpass\CoreBundle\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const EDITABLE_ROLES = [
        'ROLE_PARTICIPANTS_ADMIN',
        'ROLE_PAYMENTS_ADMIN',
        'ROLE_CMS_ADMIN',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private ?int $id = null;

    #[Assert\Email]
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $street = null;

    #[ORM\Column(type: 'string', length: 16, nullable: true)]
    private ?string $postal = null;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $city = null;
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $country = null;
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $phone = null;
    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    private ?string $mobile = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $modifiedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    #[ORM\Column(type: 'json')]
    private ?array $editableRoles = [];

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password;

    #[Assert\Length(max: 4096)]
    private ?string $plainPassword;

    #[ORM\Column(type: 'boolean')]
    private bool $disable = false;

    #[ORM\Column(type: 'boolean')]
    private bool $superAdmin = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: HostMemberAssociation::class, cascade: ['persist'])]
    private Collection $hostAssociations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Participant::class, cascade: ['persist'])]
    private Collection $participants;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->modifiedAt = new \DateTimeImmutable();
        $this->hostAssociations = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function getName(): string
    {
        return sprintf('%s %s', $this->getFirstname(), $this->getLastname());
    }

    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getPostal(): ?string
    {
        return $this->postal;
    }

    public function setPostal(?string $postal): void
    {
        $this->postal = $postal;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getModifiedAt(): \DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $dateTime = new \DateTime()): void
    {
        $this->modifiedAt = $dateTime;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        if (\in_array('ROLE_ADMIN', $roles, true) && $this->isSuperAdmin()) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }

        foreach (self::EDITABLE_ROLES as $role) {
            if (\in_array($role, $this->getEditableRoles(), true)) {
                $roles[] = $role;
            }
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getEditableRoles(): array
    {
        return (array) $this->editableRoles;
    }

    public function setEditableRoles(array $editableRoles): void
    {
        $this->editableRoles = $editableRoles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function isDisabled(): bool
    {
        return $this->disable;
    }

    public function setDisabled(bool $disable = true): void
    {
        $this->disable = $disable;
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin;
    }

    public function setSuperAdmin(bool $superAdmin): void
    {
        $this->superAdmin = $superAdmin;
    }

    public function getHosts(): Collection
    {
        return $this->hostAssociations->map(fn (HostMemberAssociation $a) => $a->getHost());
    }

    public function hasHost(Host $host): bool
    {
        return !$this->hostAssociations
            ->matching(Criteria::create()->where(Criteria::expr()->eq('host', $host)))
            ->isEmpty();
    }

    public function addHost(Host $host)
    {
        if ($this->hasHost($host)) {
            return;
        }

        $this->hostAssociations[] = new HostMemberAssociation($this, $host);
    }

    public function removeHost(Host $host)
    {
        /** @var HostMemberAssociation $hostAssociation */
        foreach ($this->hostAssociations as $hostAssociation) {
            if ($hostAssociation->getHost() === $host) {
                $this->hostAssociations->removeElement($hostAssociation);

                return;
            }
        }
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): void
    {
        $this->lastLogin = $lastLogin;
    }

    /** @return Collection<Participant> */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
