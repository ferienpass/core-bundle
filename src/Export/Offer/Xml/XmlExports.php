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

namespace Ferienpass\CoreBundle\Export\Offer\Xml;

final class XmlExports
{
    /**
     * @var array<string, string>
     */
    private array $templates = [];
    private XmlExport $xmlExport;

    public function __construct(XmlExport $xmlExport)
    {
        $this->xmlExport = $xmlExport;
    }

    public function addTemplate(string $key, string $template): void
    {
        $this->templates[$key] = $template;
    }

    public function getNames(): array
    {
        return array_keys($this->templates);
    }

    public function has(string $key): bool
    {
        return isset($this->templates[$key]);
    }

    public function get(string $key): XmlExport
    {
        if (!isset($this->templates[$key])) {
            throw new \LogicException('XML Export not supported');
        }

        return $this->xmlExport->withTemplate($this->templates[$key]);
    }
}
