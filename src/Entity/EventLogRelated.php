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
class EventLogRelated
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'EventLog', inversedBy: 'related')]
    #[ORM\JoinColumn(name: 'log_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private EventLog $logEntry;

    #[ORM\Column(type: 'text')]
    private string $relatedTable;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private int $relatedId;

    public function __construct(EventLog $logEntry, string $relatedTable, int $relatedId)
    {
        $this->logEntry = $logEntry;
        $this->relatedTable = $relatedTable;
        $this->relatedId = $relatedId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLogEntry(): EventLog
    {
        return $this->logEntry;
    }

    public function getRelatedTable(): string
    {
        return $this->relatedTable;
    }

    public function getRelatedId(): int
    {
        return $this->relatedId;
    }
}
