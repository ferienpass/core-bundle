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
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\Model\Collection;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'home_news_list', category: 'texts')]
class HomeNewsList extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if ($this->container->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');

            $template->title = 'Neuigkeiten';
            $template->wildcard = 'Liste mit hervorgehobenen Nachrichten';

            $template->href = $this->container->get('router')->generate('contao_backend', ['do' => 'news']);
            $template->link = 'Nachrichten verfassen';

            return new Response($template->parse());
        }

        $articles = $this->fetchItems();
        if (null === $articles) {
            return new Response();
        }

        return $this->render('@FerienpassCore/ContentElement/home-news-list.html.twig', [
            'articles' => array_map(
                fn ($article) => $this->parseArticle($article),
                iterator_to_array($articles, false)
            ),
        ]);
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    private function fetchItems(): ?Collection
    {
        $newsArchives = NewsArchiveModel::findAll();
        if (null === $newsArchives) {
            return null;
        }

        return NewsModel::findPublishedByPids($newsArchives->fetchEach('id'), true, 0, 0, ['order' => 'tl_news.date DESC']);
    }

    private function parseArticle($newsModel)
    {
        $data = $newsModel->row();

        $content = ContentModel::findPublishedByPidAndTable($newsModel->id, 'tl_news');

        $data['content'] = null === $content
            ? []
            : array_map(
                fn (ContentModel $element) => Controller::getContentElement($element),
                iterator_to_array($content, false)
            );

        $this->tagResponse(['contao.db.tl_news.'.$newsModel->id]);
        $this->tagResponse(['contao.db.tl_news_archive.'.$newsModel->pid]);

        return $data;
    }
}
