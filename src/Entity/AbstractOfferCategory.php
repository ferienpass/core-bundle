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
 * @ORM\MappedSuperclass()
 */
abstract class AbstractOfferCategory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private int $id;

    /**
     * @ORM\Column(name="tstamp", type="integer", options={"unsigned"=true})
     */
    private int $timestamp;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"default"=""})
     */
    private string $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, options={"default"=""}, unique=true)
     */
    private string $alias;
}
