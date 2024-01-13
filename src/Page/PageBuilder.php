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

namespace Ferienpass\CoreBundle\Page;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\NoLayoutSpecifiedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContext;
use Contao\CoreBundle\Util\LocaleUtil;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\LayoutModel;
use Contao\ModuleModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PageBuilder
{
    /** @var array<string,ControllerReference[]> */
    private array $sections = [];

    private FrontendTemplate $template;
    private string $templateName;
    private string $pageTitle;

    private PageModel $pageModel;
    private ResponseContext $responseContext;

    public function __construct(
        PageModel $pageModel,
        private FragmentHandler $fragmentHandler,
        private TranslatorInterface $translator,
        private ContaoFramework $framework,
        private RequestStack $requestStack
    ) {
        $this->pageModel = $pageModel->loadDetails();
    }

    public function withTemplate(string $template): self
    {
        $new = clone $this;

        $new->templateName = $template;

        return $new;
    }

    public function setPageTitle(string $pageTitle): self
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    public function getPageTitle(): string
    {
        return $this->pageTitle;
    }

    public function addFragment(string $section, ControllerReference $fragment): self
    {
        $this->sections[$section][] = $fragment;

        return $this;
    }

    public function getResponse(): Response
    {
        return $this->buildPage()->getResponse();
    }

    private function buildPage(): FrontendTemplate
    {
        $this->prepare();

        $layoutModel = $this->getPageLayout();
        $themeModel = $layoutModel->getRelated('pid');

        // Set the default image densities
        // $container->get('contao.image.picture_factory')->setDefaultDensities($layoutModel->defaultImageDensities);

        $this->pageModel->layoutId = $layoutModel->id;
        $this->pageModel->template = $this->templateName = $layoutModel->template ?: 'fe_page';
        $this->pageModel->templateGroup = $themeModel->templates ?? null;

        $template = $this->createTemplate();

        $this->processFragments($template);

        if (isset($GLOBALS['TL_HOOKS']['generatePage']) && \is_array($GLOBALS['TL_HOOKS']['generatePage'])) {
            foreach ($GLOBALS['TL_HOOKS']['generatePage'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($this->pageModel, $layoutModel);
            }
        }

        $headBag = $this->responseContext->get(HtmlHeadBag::class);

        $template->mainTitle = $this->pageModel->rootPageTitle;
        $template->pageTitle = htmlspecialchars($headBag->getTitle());
        $template->mainTitle = str_replace('[-]', '', $template->mainTitle);
        $template->pageTitle = str_replace('[-]', '', $template->pageTitle);
        $template->robots = htmlspecialchars($headBag->getMetaRobots());

        if (!$layoutModel->titleTag) {
            $layoutModel->titleTag = '{{page::pageTitle}} - {{page::rootPageTitle}}';
        }

        $template->title = strip_tags((string) System::getContainer()->get('contao.insert_tag.parser')->replaceInline($layoutModel->titleTag));
        $template->description = htmlspecialchars($headBag->getMetaDescription());

        return $template;
    }

    private function prepare(): void
    {
        $GLOBALS['objPage'] = $this->pageModel;
        $this->requestStack->getCurrentRequest()->attributes->set('pageModel', $this->pageModel);

        $locale = LocaleUtil::formatAsLocale($this->pageModel->language);

        if (null !== $request = $this->requestStack->getCurrentRequest()) {
            $request->setLocale($locale);
        }

        if ($this->translator instanceof LocaleAwareInterface) {
            $this->translator->setLocale($locale);
        }

        $this->responseContext = System::getContainer()->get('contao.routing.response_context_factory')->createContaoWebpageResponseContext($this->pageModel);

        System::loadLanguageFile('default');

        $this->framework->initialize();
    }

    private function initializeSections(LayoutModel $layoutModel, Template $template): void
    {
        $customSections = $template->sections ?? [];
        $arrSections = ['header', 'left', 'right', 'main', 'footer'];
        $modules = StringUtil::deserialize($layoutModel->modules);

        $moduleIds = [];

        foreach ($modules as $module) {
            if ($module['enable']) {
                $moduleIds[] = $module['mod'];
            }
        }

        $moduleModels = ModuleModel::findMultipleByIds($moduleIds);

        if (null !== $moduleModels) {
            $mapper = [];
            while ($moduleModels->next()) {
                $mapper[$moduleModels->id] = $moduleModels->current();
            }

            foreach ($modules as $include) {
                // Skip articles
                if (!$include['enable'] || '0' === $include['mod']) {
                    continue;
                }

                if ($include['mod'] > 0 && isset($mapper[$include['mod']])) {
                    $include['mod'] = $mapper[$include['mod']];
                }

                if (\in_array($include['col'], $arrSections, true)) {
                    $template->{$include['col']} .= Controller::getFrontendModule($include['mod'], $include['col']);
                } else {
                    $customSections[$include['col']] .= Controller::getFrontendModule($include['mod'], $include['col']);
                }
            }
        }

        $template->sections = $customSections;
    }

    private function processFragments(FrontendTemplate $template): void
    {
        $sections = $template->sections ?? [];

        foreach ($this->sections as $section => $fragments) {
            foreach ($fragments as $fragment) {
                if ('main' === $section) {
                    $template->main .= $this->fragmentHandler->render($fragment);
                } else {
                    $sections[$section] .= $this->fragmentHandler->render($fragment);
                }
            }
        }

        $template->sections = $sections;
    }

    private function createTemplate(): FrontendTemplate
    {
        $template = $this->framework->createInstance(FrontendTemplate::class, [$this->templateName]);

        $template->language = $GLOBALS['TL_LANGUAGE'];
        $template->charset = Config::get('characterSet');
        $template->base = Environment::get('base');

        return $template;
    }

    private function getPageLayout(): LayoutModel
    {
        $layoutModel = LayoutModel::findByPk($this->pageModel->layout);
        if (null === $layoutModel) {
            throw new NoLayoutSpecifiedException('No layout specified');
        }

        if (isset($GLOBALS['TL_HOOKS']['getPageLayout']) && \is_array($GLOBALS['TL_HOOKS']['getPageLayout'])) {
            foreach ($GLOBALS['TL_HOOKS']['getPageLayout'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}($this->pageModel, $layoutModel);
            }
        }

        return $layoutModel;
    }
}
