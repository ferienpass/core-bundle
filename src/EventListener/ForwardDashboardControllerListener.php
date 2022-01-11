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

namespace Ferienpass\CoreBundle\EventListener;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;
use Ferienpass\CoreBundle\Controller\Backend\DashboardController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ForwardDashboardControllerListener
{
    public function __construct(private RequestStack $requestStack, private HttpKernelInterface $httpKernel)
    {
    }

    /**
     * @Hook("parseTemplate")
     */
    public function onInitializeSystem(Template $template): void
    {
        if ('be_welcome' === $template->getName()) {
            throw new ResponseException($this->forwardRequest(DashboardController::class));
        }
    }

    private function forwardRequest(string $controller): Response
    {
        $request = $this->requestStack->getCurrentRequest();

        $path = array_merge($request->attributes->all(), ['_controller' => $controller]);

        $subRequest = $request->duplicate([], null, $path);

        return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
