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
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'Ferienpass\CoreBundle\Repository\HostRepository')]
#[UniqueEntity('alias')]
class Host
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[Groups('admin_list')]
    private int $id;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Groups('admin_list')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    #[Assert\NotBlank(message: 'notBlank')]
    #[Groups('admin_list')]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private ?string $alias = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    #[PhoneNumber(defaultRegion: 'DE')]
    #[Groups('admin_list')]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    #[PhoneNumber(defaultRegion: 'DE')]
    #[Groups('admin_list')]
    private ?string $fax = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[PhoneNumber(type: PhoneNumber::MOBILE, defaultRegion: 'DE')]
    #[Groups('admin_list')]
    private ?string $mobile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Email]
    #[Groups('admin_list')]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Url]
    #[Groups('admin_list')]
    private ?string $website = null;

    #[ORM\Column(type: 'string', length: 32, nullable: true)]
    #[Groups('admin_list')]
    private ?string $postal = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    #[Groups('admin_list')]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 64, nullable: true)]
    #[Groups('admin_list')]
    private ?string $street = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups('admin_list')]
    private ?string $text = null;

    #[ORM\Column(type: 'binary_string', length: 16, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: 'string', length: 1, nullable: true)]
    private ?string $active = null;

    #[ORM\OneToMany(mappedBy: 'host', targetEntity: HostMemberAssociation::class, cascade: ['persist'])]
    private Collection $memberAssociations;

    #[ORM\ManyToMany(targetEntity: Offer::class, mappedBy: 'hosts')]
    private Collection $offers;

    public function __construct()
    {
        $this->memberAssociations = new ArrayCollection();
        $this->offers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function hasMember(User $user): bool
    {
        return !$this->memberAssociations
            ->matching(Criteria::create()->where(Criteria::expr()->eq('user', $user)))
            ->isEmpty();
    }

    public function addMember(User $user): self
    {
        if ($this->hasMember($user)) {
            return $this;
        }

        return $this->addMemberAssociation(new HostMemberAssociation($user, $this));
    }

    public function addMemberAssociation(HostMemberAssociation $memberAssociation): self
    {
        $this->memberAssociations[] = $memberAssociation;

        return $this;
    }

    public function removeMemberAssociation(HostMemberAssociation $memberAssociation): void
    {
        $this->memberAssociations->removeElement($memberAssociation);
    }

    public function getMemberAssociations(): Collection
    {
        return $this->memberAssociations;
    }

    public function getUsers(): array
    {
        return array_filter($this->memberAssociations->map(fn (HostMemberAssociation $a) => $a->getUser())->toArray());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getPostal(): ?string
    {
        return $this->postal;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setAlias(?string $alias): void
    {
        $this->alias = $alias;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function setFax(?string $fax): void
    {
        $this->fax = $fax;
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function setWebsite(?string $website): void
    {
        $this->website = $website;
    }

    public function setPostal(?string $postal): void
    {
        $this->postal = $postal;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function setText(?string $text): void
    {
        $this->text = $text;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }

    public function setActive(?string $active): void
    {
        $this->active = $active;
    }

    public function getOffers(): Collection
    {
        return $this->offers;
    }

    public function addOffer(Offer $offer): void
    {
        $this->offers->add($offer);
    }

    public function generateAlias(SluggerInterface $slugger)
    {
        if (!$this->alias) {
            $this->alias = (string) $slugger->slug($this->getName() ?? '')->lower();
        }
    }
}
