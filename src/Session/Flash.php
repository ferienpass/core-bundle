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

namespace Ferienpass\CoreBundle\Session;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatableInterface;

class Flash
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function addConfirmation(string|TranslatableInterface $headline = null, string|TranslatableInterface $text = null, bool $dismissable = false, string|TranslatableInterface $linkText = null, string $href = null)
    {
        $this->add('confirmation', $headline, $text, $dismissable, $linkText, $href);
    }

    public function addError(string|TranslatableInterface $headline = null, string|TranslatableInterface $text = null, bool $dismissable = false, string|TranslatableInterface $linkText = null, string $href = null)
    {
        $this->add('error', $headline, $text, $dismissable, $linkText, $href);
    }

    public function addInfoBanner(string|TranslatableInterface $headline = null, string|TranslatableInterface $text = null, bool $dismissable = false, string|TranslatableInterface $linkText = null, string $href = null)
    {
        $this->add('banner-info', $headline, $text, $dismissable, $linkText, $href);
    }

    public function addConfirmationModal(string|TranslatableInterface $headline = null, string|TranslatableInterface $text = null, bool $dismissable = false, string|TranslatableInterface $linkText = null, string $href = null)
    {
        $this->add('modal-confirm', $headline, $text, $dismissable, $linkText, $href);
    }

    public function addErrorModal(string|TranslatableInterface $headline = null, string|TranslatableInterface $text = null, bool $dismissable = false, string|TranslatableInterface $linkText = null, string $href = null)
    {
        $this->add('modal-error', $headline, $text, $dismissable, $linkText, $href);
    }

    private function add(string $type, string|TranslatableInterface $headline = null, string|TranslatableInterface $text = null, bool $dismissable = false, string|TranslatableInterface $linkText = null, string $href = null): void
    {
        try {
            $this->requestStack->getSession()->getFlashBag()->add($type, [
                'headline' => $headline,
                'text' => $text,
                'dismissable' => $dismissable,
                'linkText' => $linkText,
                'href' => $href,
            ]);
        } catch (SessionNotFoundException $e) {
            throw new \LogicException('You cannot use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".', 0, $e);
        }
    }
}
