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

namespace Ferienpass\CoreBundle\Controller\Fragment;

use Contao\CoreBundle\Controller\AbstractController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\PageModel;
use Ferienpass\CoreBundle\Form\OfferFiltersType;
use Ferienpass\CoreBundle\Repository\EditionRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

final class OfferListFilterController extends AbstractController
{
    public function __construct(private EditionRepository $editionRepository)
    {
    }

    public function __invoke(Request $request, Session $session): Response
    {
        $hasEditions = $this->editionRepository->count([]) > 0;
        $edition = $this->editionRepository->findOneToShow(PageModel::findByPk($request->attributes->get('pageModel')));

        if ($hasEditions && null === $edition) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        // Get the normalized form data from query
        if ($request->query->count()) {
            $form = $this->createForm(OfferFiltersType::class);
            $form->submit($request->query->all());
            $data = $form->getData();
        }

        // Build the short form
        $shortForm = $this->createForm(OfferFiltersType::class, $data ?? null, ['short' => true]);

        // If filters form submitted, redirect to a pretty URL
        $shortForm->handleRequest($request);
        if ($shortForm->isSubmitted() && $shortForm->isValid()) {
            throw new RedirectResponseException($this->getFilterUrl($shortForm, $request));
        }

        // Build the full form
        $form = $this->createForm(OfferFiltersType::class, $data ?? null);
        $form->handleRequest($request);

        // If filters form submitted, redirect to a pretty URL
        if ($form->isSubmitted() && $form->isValid()) {
            throw new RedirectResponseException($this->getFilterUrl($form, $request));
        }

        return $this->renderForm('@FerienpassCore/Fragment/offer_list_filter.html.twig', [
            'shortForm' => $shortForm,
            'fullForm' => $form,
        ]);
    }

    private function getFilterUrl(FormInterface $form, Request $request): string
    {
        $params = HeaderUtils::parseQuery((string) $request->getQueryString());

        foreach (array_keys((array) $form->getViewData()) as $attr) {
            if (!$form->has($attr) || $form->get($attr)->isEmpty()) {
                unset($params[$attr]);
                continue;
            }

            $value = $form->get($attr)->getViewData();
            $params[$attr] = $value;
        }

        // New filter settings are not compatible with pagination
        unset($params['page']);

        $qs = \count($params) ? '?'.http_build_query($params) : '';

        return $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo().$qs;
    }
}
