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

namespace Ferienpass\CoreBundle\Fixtures\Factory;

use DateTime;
use Ferienpass\CoreBundle\Entity\EditionTask;
use Zenstruck\Foundry\ModelFactory;

class EditionTaskFactory extends ModelFactory
{
    public function ofTypeHoliday(): self
    {
        return $this->addState(['type' => 'holiday']);
    }

    public function ofTypeHostEditingStage(): self
    {
        return $this->addState(['type' => 'host_editing_stage']);
    }

    public function ofTypeShowOffers(): self
    {
        return $this->addState(['type' => 'show_offers']);
    }

    public function ofTypeFirstComeApplicationSystem(): self
    {
        return $this->addState(['type' => 'application_system', 'applicationSystem' => 'firstcome']);
    }

    public function ofTypeLotApplicationSystem(): self
    {
        return $this->addState(['type' => 'application_system', 'applicationSystem' => 'lot']);
    }

    public function isInPast(): self
    {
        return $this->addState(['periodEnd' => new DateTime('-2 seconds')]);
    }

    public function withEdition($edition)
    {
        return $this->addState(['edition' => $edition]);
    }

    protected function getDefaults(): array
    {
        return [
            'timestamp' => time(),
            'sorting' => 0,
            'periodBegin' => new DateTime('-1 week'),
            'periodEnd' => new DateTime('+2 months'),
        ];
    }

    protected function initialize(): self
    {
        // see https://github.com/zenstruck/foundry#initialization
        return $this// ->afterInstantiate(function(Post $post) {})
            ;
    }

    protected static function getClass(): string
    {
        return EditionTask::class;
    }
}
