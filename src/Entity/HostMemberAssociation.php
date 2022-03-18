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

/**
 * @ORM\Entity
 */
class HostMemberAssociation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $id;

    /**
     * @ORM\Column(name="member_id", type="integer", options={"unsigned"=true})
     */
    private int $member;

    /**
     * @ORM\ManyToOne(targetEntity="Ferienpass\CoreBundle\Entity\Host", inversedBy="memberAssociations")
     * @ORM\JoinColumn(name="host_id", referencedColumnName="id")
     */
    private Host $host;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private \DateTimeInterface $createdAt;

    public function __construct(int $member, Host $host)
    {
        $this->createdAt = new \DateTimeImmutable();

        $this->member = $member;
        $this->host = $host;
    }

    public function getMember(): int
    {
        return $this->member;
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
