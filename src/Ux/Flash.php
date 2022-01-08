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

namespace Ferienpass\CoreBundle\Ux;

use Symfony\Contracts\Translation\TranslatableInterface;

class Flash
{
    private string $type;
    private array $message;

    private function __construct(string $type)
    {
        $this->type = $type;

        $this->message = [
            'headline' => null,
            'text' => null,
            'dismissable' => false,
            'linkText' => null,
            'href' => null,
        ];
    }

    public static function confirmation(): self
    {
        return new self('confirmation');
    }

    public static function error(): self
    {
        return new self('error');
    }

    public static function infoBanner(): self
    {
        return new self('banner-info');
    }

    public static function confirmationModal(): self
    {
        return new self('modal-confirm');
    }

    /**
     * @param string|TranslatableInterface $text
     */
    public function text($text): self
    {
        $this->message['text'] = $text;

        return $this;
    }

    public function headline(string $headline): self
    {
        $this->message['headline'] = $headline;

        return $this;
    }

    public function dismissable(): self
    {
        $this->message['dismissable'] = true;

        return $this;
    }

    public function linkText(string $linkText): self
    {
        $this->message['linkText'] = $linkText;

        return $this;
    }

    public function href(string $href): self
    {
        $this->message['href'] = $href;

        return $this;
    }

    public function create(): array
    {
        return [$this->type, $this->message];
    }
}
