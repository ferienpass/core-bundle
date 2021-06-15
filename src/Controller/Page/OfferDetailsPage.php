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

namespace Ferienpass\CoreBundle\Controller\Page;

use Contao\CoreBundle\Exception\InsufficientAuthenticationException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\FrontendUser;
use Contao\PageModel;
use Ferienpass\CoreBundle\Controller\Frontend\AbstractController;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Fragment\FragmentReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OfferDetailsPage extends AbstractController
{
    public function __invoke(Offer $offer, Request $request): Response
    {
        if (!$request->attributes->getBoolean('preview') && !$offer->isPublished()) {
            throw new PageNotFoundException();
        }

        $this->initializeContaoFramework();

        $pageModel = $request->attributes->get('pageModel');
        if ($pageModel instanceof PageModel) {
            $pageModel->title = $offer->getName();

            if ($date = $offer->getDates()->first()) {
                $pageModel->title .= sprintf(' (%s)', $date->getBegin()->format('d.m.Y'));
            }

            $pageModel->title .= ' - '.implode(', ', $offer->getHosts()->map(fn (Host $h) => $h->getName())->toArray());
        }

        if ($request->query->has('login')) {
            $user = $this->getUser();
            if (!$user instanceof FrontendUser) {
                throw new InsufficientAuthenticationException();
            }

            return $this->redirectToRoute('offer_details', ['alias' => $offer->getAlias()]);
        }

        return $this->createPageBuilder($pageModel)
            ->addFragment('main', new FragmentReference('ferienpass.fragment.offer_details', ['id' => $offer->getId()]))
            ->addFragment('main', new FragmentReference('ferienpass.fragment.application_form', ['id' => $offer->getId()]))
            ->getResponse()
            ;
    }
}
