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

namespace Ferienpass\CoreBundle\Controller\Fragment;

use Contao\ModuleChangePassword;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Response;

final class ChangePassword extends ModuleChangePassword
{
    protected $strTemplate = 'mod_changePassword_main';

    /** @noinspection MagicMethodsValidityInspection */

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
    }

    public function __invoke(): Response
    {
        $moduleModel = new ModuleModel();

        $data = [];

        parent::__construct($moduleModel->setRow($data), 'main');

        return new Response($this->generate());
    }
}
