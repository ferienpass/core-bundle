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

use Contao\CoreBundle\Controller\AbstractController;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Response;

final class NotificationsController extends AbstractController
{
    public function __invoke(): Response
    {
        return new Response('asdf');

        $moduleModel = new ModuleModel();

        $data = [
            'nc_member_customizable_notifications' => [1, 7],
            'nc_member_customizable_mandatory' => true,
            'nc_member_customizable_inputType' => 'checkbox',
        ];

        parent::__construct($moduleModel->setRow($data), 'main');

        return new Response($this->generate());
    }
}
