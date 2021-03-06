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

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\StringUtil;
use Contao\Template;
use Ferienpass\CoreBundle\Entity\Edition;
use Ferienpass\CoreBundle\Entity\EditionTask;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("countdown", category="ferienpass")
 */
class CountdownController extends AbstractContentElementController
{
    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        /** @var EditionRepository $editionRepo */
        $editionRepo = $this->getDoctrine()->getRepository(Edition::class);
        $passEdition = $editionRepo->findOneClosestByTask('show_offers');
        if (null === $passEdition) {
            return new Response('Fehler. Zeitraum nicht festgelegt.');
        }

        /** @var EditionTask $editionTask */
        $editionTask = $passEdition->getShowOfferPeriods()->current();

        return $this->render('@FerienpassCore/ContentElement/countdown.html.twig', [
            'headline' => StringUtil::deserialize($model->headline, true)['value'] ?? '',
            'task' => $editionTask,
        ]);
    }
}
