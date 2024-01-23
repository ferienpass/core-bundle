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

namespace Ferienpass\CoreBundle\Twig\Mime;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class NotificationEmail extends TemplatedEmail
{
    private array $context = [
        'content' => '',
        'action_text' => null,
        'action_url' => null,
        'footer_text' => 'Notification email sent by Symfony',
    ];

    public function __construct(private readonly string $type)
    {
        parent::__construct();
    }

    public function __serialize(): array
    {
        return [$this->type, parent::__serialize()];
    }

    public function __unserialize(array $data): void
    {
        [$this->type, $parentData] = $data;

        parent::__unserialize($parentData);
    }

    /**
     * @return $this
     */
    public function content(string $content, bool $raw = false): static
    {
        $this->context['content'] = $content;
        $this->context['raw'] = $raw;

        return $this;
    }

    /**
     * @return $this
     */
    public function action(string $text, string $url): static
    {
        $this->context['action_text'] = $text;
        $this->context['action_url'] = $url;

        return $this;
    }

    public function getHtmlTemplate(): ?string
    {
        return '@FerienpassCore/Email/'.$this->type.'.html.twig';
    }

    public function getContext(): array
    {
        return array_merge($this->context, parent::getContext());
    }
}
