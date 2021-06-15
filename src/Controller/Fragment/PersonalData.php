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

use Contao\ModuleModel;
use Contao\ModulePersonalData;
use Symfony\Component\HttpFoundation\Response;

final class PersonalData extends ModulePersonalData
{
    protected $strTemplate = 'member_personal_data';

    /** @noinspection MagicMethodsValidityInspection */

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
    }

    public function __invoke(): Response
    {
        $moduleModel = new ModuleModel();

        $data = [
            'editable' => [
                'firstname',
                'lastname',
                'phone',
                'mobile',
                'email',
            ],
        ];

        parent::__construct($moduleModel->setRow($data), 'main');

        return new Response($this->generate());
    }
}
