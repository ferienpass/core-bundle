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

namespace Ferienpass\CoreBundle\EventListener\DataContainer\Member;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Repository\HostRepository;

class LoadListener
{
    private HostRepository $hostRepository;

    public function __construct(HostRepository $hostRepository)
    {
        $this->hostRepository = $hostRepository;
    }

    /**
     * @Callback(table="tl_member", target="config.onload", priority=10)
     */
    public function onLoadCallback(DataContainer $dc = null): void
    {
        if (null === $dc) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_member']['fields']['login']['filter'] = false;
        $GLOBALS['TL_DCA']['tl_member']['fields']['city']['filter'] = false;
        $GLOBALS['TL_DCA']['tl_member']['fields']['disable']['filter'] = false;
        $GLOBALS['TL_DCA']['tl_member']['fields']['username']['search'] = false;
        $GLOBALS['TL_DCA']['tl_member']['fields']['company']['search'] = false;

        if ('member_hosts' === Input::get('do')) {
            $GLOBALS['TL_DCA']['tl_member']['list']['sorting']['filter'] = "`groups` LIKE '%\"1\"%'";
            $GLOBALS['TL_DCA']['tl_member']['fields']['groups']['filter'] = false;
            $GLOBALS['TL_DCA']['tl_member']['list']['label']['fields'][] = 'hosts';
        }

        if ('member_parents' === Input::get('do')) {
            $GLOBALS['TL_DCA']['tl_member']['list']['sorting']['filter'][] = "`groups` LIKE '%\"2\"%'";
            $GLOBALS['TL_DCA']['tl_member']['fields']['groups']['filter'] = false;
            $GLOBALS['TL_DCA']['tl_member']['fields']['hosts']['filter'] = false;
        }
    }

    /**
     * @Callback(table="tl_member", target="list.label.label")
     */
    public function labelCallback(array $row, string $label, DataContainer $dc, array $labels)
    {
        $labels = System::importStatic('tl_member')->addIcon(...\func_get_args());

        $hosts = $this->hostRepository->findByMemberId((int) $row['id']);
        $labels[4] = implode(', ', array_map(fn (Host $h) => $h->getName(), $hosts));

        return $labels;
    }
}
