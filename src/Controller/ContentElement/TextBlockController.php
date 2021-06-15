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

namespace Ferienpass\CoreBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Asset\ContaoContext;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("text_block", category="texts")
 */
class TextBlockController extends AbstractContentElementController
{
    private ContaoContext $assetsFilesContext;
    private string $projectDir;

    public function __construct(ContaoContext $assetsFilesContext, string $projectDir)
    {
        $this->assetsFilesContext = $assetsFilesContext;
        $this->projectDir = $projectDir;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        $headline = StringUtil::deserialize($model->headline);
        $text = StringUtil::toHtml5($model->text);

        if ($this->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');

            $template->title = $headline['value'];
            $template->wildcard = strip_tags($text);
            $template->noWildcard = true;

            return new Response($template->parse());
        }

        // Add the static files URL to images
        if ($staticUrl = $this->assetsFilesContext->getStaticUrl()) {
            $path = Config::get('uploadPath').'/';
            $text = str_replace(' src="'.$path, ' src="'.$staticUrl.$path, $text);
        }

        $text = StringUtil::encodeEmail($text);

        if ($model->addImage && $model->singleSRC) {
            $filesModel = FilesModel::findByUuid($model->singleSRC);

            if (null !== $filesModel && is_file($this->projectDir.'/'.$filesModel->path)) {
                $singleSRC = $filesModel->path;
            }
        }

        switch ($model->block_layout) {
            case 'max-w+center':
                $maxWidth = 'narrow';
                $align = 'center';
                break;
            case 'max-w':
                $maxWidth = 'narrow';
                break;
        }

        return $this->render('@FerienpassCore/Fragment/text-block.html.twig', [
            'headline' => \is_array($headline) ? $headline['value'] : $headline,
            'hl' => \is_array($headline) ? $headline['unit'] : 'h1',
            'text' => $text,
            'maxWidth' => $maxWidth ?? null,
            'align' => $align ?? null,
            'image' => $singleSRC ?? null,
        ]);
    }
}
