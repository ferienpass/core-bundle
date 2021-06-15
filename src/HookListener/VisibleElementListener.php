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

namespace Ferienpass\CoreBundle\HookListener;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Ferienpass\CoreBundle\Repository\EditionRepository;

class VisibleElementListener
{
    private EditionRepository $passEditionRepository;
    private TokenChecker $tokenChecker;

    public function __construct(EditionRepository $passEditionRepository, TokenChecker $tokenChecker)
    {
        $this->tokenChecker = $tokenChecker;
        $this->passEditionRepository = $passEditionRepository;
    }

    /**
     * @psalm-suppress UndefinedConstant
     */
    public function onIsVisibleElement(Model $element, bool $visible): bool
    {
        if (false === $visible) {
            return false;
        }

        if ('FE' === TL_MODE && $element->ferienpass_task_condition) {
            if ($this->tokenChecker->isPreviewMode()) {
                // Do not apply further logic.
                return $visible;
            }

            $passEdition = $this->passEditionRepository->findOneWithActiveTask((string) $element->ferienpass_task_condition);

            if ($element->ferienpass_task_condition_inverted) {
                return null === $passEdition;
            }

            return null !== $passEdition;
        }

        return $visible;
    }
}
