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
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(type: 'greeting_with_picture', category: 'texts')]
class GreetingWithPicture extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        $headline = StringUtil::deserialize($model->headline);
        $text = $model->text;

        if ($this->container->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');

            $template->title = $headline['value'];
            $template->wildcard = strip_tags($text);
            $template->noWildcard = true;

            return new Response($template->parse());
        }

        $text = StringUtil::encodeEmail($text);

        return $this->render('@FerienpassCore/ContentElement/greeting-with-picture.html.twig', [
            'headline' => \is_array($headline) ? $headline['value'] : $headline,
            'hl' => \is_array($headline) ? $headline['unit'] : 'h1',
            'text' => $text,
            'image' => $model->singleSRC,
        ]);
    }
}
