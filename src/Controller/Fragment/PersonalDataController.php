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

use Contao\CoreBundle\Controller\AbstractFragmentController;
use Contao\FrontendUser;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Form\UserPersonalDataType;
use Ferienpass\CoreBundle\Ux\Flash;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PersonalDataController extends AbstractFragmentController
{
    public function __construct(private FormFactoryInterface $formFactory)
    {
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof FrontendUser) {
            return new Response('', Response::HTTP_NO_CONTENT);
        }

        $memberModel = MemberModel::findByPk($user->id);

        $form = $this->formFactory->create(UserPersonalDataType::class, $memberModel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Fix some "Column cannot be null" errors
            $memberModel->street ??= '';
            $memberModel->postal ??= '';
            $memberModel->city ??= '';
            $memberModel->phone ??= '';
            $memberModel->mobile ??= '';
            $memberModel->country = strtolower($memberModel->country ?? '');

            $memberModel->save();

            $this->addFlash(...Flash::confirmation()->text('Die Daten wurden erfolgreich gespeichert.')->create());
        }

        return $this->render('@FerienpassCore/Fragment/user_account/personal_data.html.twig', ['form' => $form]);
    }
}
