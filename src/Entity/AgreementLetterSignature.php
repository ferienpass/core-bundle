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
use Ferienpass\CoreBundle\Repository\AgreementLetterSignaturesRepository;

#[ORM\Entity(repositoryClass: AgreementLetterSignaturesRepository::class)]
class AgreementLetterSignature extends Consent
{
    #[ORM\ManyToOne(targetEntity: Edition::class)]
    private Edition $edition;

    public function __construct(User $user, Edition $edition, string $hash)
    {
        parent::__construct($user, $hash);

        $this->edition = $edition;
    }

    public static function fromEdition(Edition $edition, User $user): self
    {
        return new self($user, $edition, md5($edition->getAgreementLetterText()));
    }

    public function getEdition(): Edition
    {
        return $this->edition;
    }
}
