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

namespace Ferienpass\CoreBundle\Routing;

use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;

/**
 * This route loader overrides the Contao Core routing collection
 * to use the /admin prefix instead of /contao for back end routes.
 */
class RouteLoader extends AnnotationDirectoryLoader
{
    /**
     * @psalm-suppress ParamNameMismatch
     *
     * @param mixed      $path
     * @param mixed|null $type
     */
    public function load($path, $type = null)
    {
        $routeCollection = parent::load($path, $type);

        foreach ($routeCollection as $route) {
            $route->setPath(preg_replace('/^\/contao(.+?)?/', '/admin$1', $route->getPath()));
        }

        return $routeCollection;
    }

    public function supports($resource, $type = null)
    {
        return '@ContaoCoreBundle/Controller' === $resource && 'annotation' === $type;
    }
}
