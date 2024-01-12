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
class HostMemberAssociation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'Ferienpass\CoreBundle\Entity\User', inversedBy: 'hostAssociations')]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\ManyToOne(targetEntity: 'Ferienpass\CoreBundle\Entity\Host', inversedBy: 'memberAssociations')]
    #[ORM\JoinColumn(name: 'host_id', referencedColumnName: 'id')]
    private Host $host;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTimeInterface $createdAt;

    public function __construct(User $user = null, Host $host = null)
    {
        $this->createdAt = new \DateTimeImmutable();

        if (null !== $user) {
            $this->user = $user;
        }

        if (null !== $host) {
            $this->host = $host;
        }
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getHost(): Host
    {
        return $this->host;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
