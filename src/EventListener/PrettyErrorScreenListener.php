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

use Contao\CoreBundle\Exception\InvalidRequestTokenException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\StringUtil;
use Ferienpass\CoreBundle\Fragment\FragmentReference;
use Ferienpass\CoreBundle\Page\PageBuilderFactory;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Twig\Environment;
use Twig\Error\Error;

class PrettyErrorScreenListener
{
    private bool $prettyErrorScreens;
    private Environment $twig;
    private Packages $assetPackages;
    private string $logo;
    private PageBuilderFactory $pageBuilderFactory;

    public function __construct(bool $prettyErrorScreens, Environment $twig, Packages $assetPackages, string $logo, PageBuilderFactory $pageBuilderFactory)
    {
        $this->prettyErrorScreens = $prettyErrorScreens;
        $this->twig = $twig;
        $this->assetPackages = $assetPackages;
        $this->logo = $logo;
        $this->pageBuilderFactory = $pageBuilderFactory;
    }

    public function __invoke(ExceptionEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        if ('html' !== $request->getRequestFormat()) {
            return;
        }

        if (!AcceptHeader::fromString($request->headers->get('Accept'))->has('text/html')) {
            return;
        }

        $this->handleException($event);
    }

    private function handleException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof ServiceUnavailableHttpException) {
            $this->renderTemplate('service_unavailable', 503, $event);

            return;
        }

        switch (true) {
            case $exception instanceof UnauthorizedHttpException:
                $this->renderErrorScreenByType(401, $event);
                break;

            case $exception instanceof AccessDeniedHttpException:
                $this->renderErrorScreenByType(403, $event);
                break;

            case $exception instanceof NotFoundHttpException:
                $this->renderErrorScreenByType(404, $event);
                break;

            default:
                $this->renderErrorScreenByException($event);
        }
    }

    private function renderErrorScreenByType(int $type, ExceptionEvent $event): void
    {
        static $processing;

        if (true === $processing) {
            return;
        }

        $processing = true;

        if (null !== ($response = $this->getResponseFromPageHandler($type, $event->getRequest()))) {
            $event->setResponse($response);
        }

        $processing = false;
    }

    private function getResponseFromPageHandler(int $type, Request $request): ?Response
    {
        try {
            switch (true) {
                case 401 === $type:
                    return $this->pageBuilderFactory->create($request->get('pageModel'))
                        ->addFragment('main', new FragmentReference('ferienpass.fragment.error401'))
                        ->getResponse();

                case 403 === $type:
                    return $this->pageBuilderFactory->create($request->get('pageModel'))
                        ->addFragment('main', new FragmentReference('ferienpass.fragment.error403'))
                        ->getResponse();

                case 404 === $type:
                    return $this->pageBuilderFactory->create($request->get('pageModel'))
                        ->addFragment('main', new FragmentReference('ferienpass.fragment.error404'))
                        ->getResponse();
            }
        } catch (ResponseException $e) {
            return $e->getResponse();
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    private function renderTemplate(string $template, int $statusCode, ExceptionEvent $event): void
    {
        if (!$this->prettyErrorScreens) {
            return;
        }

        $view = '@FerienpassCore/Error/'.$template.'.html.twig';
        $parameters = $this->getTemplateParameters($view, $statusCode, $event);

        try {
            $event->setResponse(new Response($this->twig->render($view, $parameters), $statusCode));
        } catch (Error $e) {
            $event->setResponse(new Response($this->twig->render('@FerienpassCore/Error/error.html.twig'), 500));
        }
    }

    private function getTemplateParameters(string $view, int $statusCode, ExceptionEvent $event): array
    {
        $encoded = StringUtil::encodeEmail('info@ferienpass.online');
        $request = $event->getRequest();

        $logo = $this->assetPackages->getUrl($this->logo, 'app_main');
        if ('' === trim($logo, '/')) {
            $logo = null;
        }

        return [
            'statusCode' => $statusCode,
            'statusName' => Response::$statusTexts[$statusCode],
            'template' => $view,
            'base' => $request->getBasePath(),
            'language' => $request->getLocale(),
            'adminEmail' => '&#109;&#97;&#105;&#108;&#116;&#111;&#58;'.$encoded,
            'exception' => $event->getThrowable()->getMessage(),
            'logoSrc' => $logo,
        ];
    }

    /**
     * Checks the exception chain for a known exception.
     */
    private function renderErrorScreenByException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $statusCode = $this->getStatusCodeForException($exception);
        $template = null;

        // Look for a template
        do {
            if ($exception instanceof InvalidRequestTokenException) {
                $template = 'invalid_request_token';
            }
        } while (null === $template && null !== ($exception = $exception->getPrevious()));

        $this->renderTemplate($template ?: 'error', $statusCode, $event);
    }

    private function getStatusCodeForException(\Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return (int) $exception->getStatusCode();
        }

        return 500;
    }
}
