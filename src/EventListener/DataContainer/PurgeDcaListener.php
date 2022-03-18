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

namespace Ferienpass\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Hook;

class PurgeDcaListener
{
    private static array $purge = [
        'tl_article' => [
            'fields' => [
                'keywords',
                'teaserCssID',
                'teaser',
                'printable',
                'protected',
                'groups',
            ],
        ],
        'tl_content' => [
            'fields' => [
                'imagemargin',
                'fullsize',
                'floating',
                'protected',
                'rel',
                'useImage',
                'multiSRC',
                'orderSRC',
                'useHomeDir',
                'perRow',
                'perPage',
                'numberOfItems',
                'numberOfItems',
                'sortBy',
                'metaIgnore',
                'galleryTpl',
                'youtube',
                'posterSRC',
                'playerSize',
                'overwriteLink',
                'sliderDelay',
                'sliderSpeed',
                'sliderStartSlide',
                'sliderContinuous',
                'youtubeOptions',
                'metamodel_use_limit',
                'metamodel_limit',
                'metamodel_offset',
                'metamodel_donotindex',
                'metamodel_meta_title',
                'metamodel_meta_description',
                'vimeo',
                'vimeoOptions',
                'playerColor',
                'playerPreload',
                'playerAspect',
                'playerCaption',
                'playerStop',
                'playerStart',
                // 'playerOptions',
                'inline',
                'splashImage',
            ],
        ],
        'tl_page' => [
            'fields' => [
                'favicon',
                'robotsTxt',
                'dateFormat',
                'timeFormat',
                'datimFormat',
                'adminEmail',
                'staticFiles',
                'staticPlugins',
                'enforceTwoFactor',
            ],
        ],
        'tl_user' => [
            'fields' => [
                'fullscreen',
            ],
        ],
        'tl_member' => [
            'fields' => [
                'dateOfBirth',
                'gender',
                'state',
                'homeDir',
            ],
        ],
        'tl_module' => [
            'fields' => [
                'queryType',
                'fuzzy',
                'minKeywordLength',
                'searchType',
                'searchTpl',
                'rss_cache',
                'rss_feed',
                'rss_template',
                'reg_assignDir',
                'reg_homeDir',
            ],
        ],
        'tl_image_size',
    ];

    /**
     * @Hook("loadDataContainer")
     */
    public function __invoke(string $table)
    {
        if (!isset(static::$purge[$table])) {
            return;
        }

        if (!\is_array(static::$purge[$table])) {
            unset($GLOBALS['TL_DCA'][$table]);

            return;
        }

        foreach (static::$purge[$table] as $k => $v) {
            if (\is_array($v)) {
                foreach ($v as $kk => $vv) {
                    if (\is_array($vv)) {
                        foreach ($vv as $vvv) {
                            unset($GLOBALS['TL_DCA'][$table][$k][$kk][$vvv]);
                            continue 2;
                        }
                    }
                    unset($GLOBALS['TL_DCA'][$table][$k][$vv]);
                }
                continue;
            }
            unset($GLOBALS['TL_DCA'][$table][$k][$v]);
        }
    }
}
