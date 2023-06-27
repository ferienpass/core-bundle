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
use Contao\CoreBundle\OptIn\OptInInterface;
use Contao\Input;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Form\UserLostPasswordType;
use Ferienpass\CoreBundle\Ux\Flash;
use NotificationCenter\Model\Notification;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatableMessage;

class LostPasswordController extends AbstractController
{
    public function __construct(private LoggerInterface $logger, private OptInInterface $optIn, private PasswordHasherInterface $passwordHasher, private FormFactoryInterface $formFactory)
    {
    }

    public function __invoke(Request $request): Response
    {
        if ($request->query->has('token') && ($token = (string) $request->query->get('token'))
            && str_starts_with($token, 'pw-')) {
            return $this->setNewPassword($request);
        }

        $form = $this->formFactory->create(UserLostPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $memberModel = MemberModel::findActiveByEmailAndUsername($data['email'] ?? '');
            if (null === $memberModel) {
                sleep(2); // Wait 2 seconds while brute forcing :)
                $form->addError(new FormError($GLOBALS['TL_LANG']['MSC']['accountNotFound']));
            } else {
                return $this->sendPasswordLink($memberModel);
            }
        }

        return $this->renderForm('@FerienpassCore/Fragment/lost_password.html.twig', [
            'form' => $form,
        ]);
    }

    protected function sendPasswordLink(MemberModel $memberModel): Response
    {
        /** @var Notification|null $notification */
        $notification = Notification::findOneBy('type', 'member_password');
        if (null === $notification) {
            throw new \RuntimeException('No notification for password reset found!');
        }

        $optInToken = $this->optIn->create('pw', $memberModel->email, ['tl_member' => [(int) $memberModel->id]]);

        $tokens = [];

        // Add member tokens
        foreach ($memberModel->row() as $k => $v) {
            $tokens['member_'.$k] = $v;
        }

        $tokens['recipient_email'] = $memberModel->email;
        $tokens['link'] = $this->generateUrl('lost_password', [
            'token' => $optInToken->getIdentifier(),
        ], RouterInterface::ABSOLUTE_URL);

        $notification->send($tokens, $GLOBALS['TL_LANGUAGE']);

        $this->logger->info('A new password has been requested for user ID '.$memberModel->id);

        return $this->redirectToRoute('lost_password_confirm');
    }

    protected function setNewPassword(Request $request): Response
    {
        // Find an unconfirmed token with only one related record
        if ((!$optInToken = $this->optIn->find(Input::get('token')))
            || !$optInToken->isValid()
            || 1 !== \count($related = $optInToken->getRelatedRecords())
            || 'tl_member' !== key($related)
            || 1 !== (is_countable($arrIds = current($related)) ? \count($arrIds = current($related)) : 0)
            || (!$memberModel = MemberModel::findByPk($arrIds[0]))) {
            return $this->render('@FerienpassCore/Fragment/message.html.twig', [
                'error' => new TranslatableMessage('MSC.invalidToken', [], 'contao_default'),
            ]);
        }

        if ($optInToken->isConfirmed()) {
            return $this->render('@FerienpassCore/Fragment/message.html.twig', [
                'error' => new TranslatableMessage('MSC.tokenConfirmed', [], 'contao_default'),
            ]);
        }

        if ($optInToken->getEmail() !== $memberModel->email) {
            return $this->render('@FerienpassCore/Fragment/message.html.twig', [
                'error' => new TranslatableMessage('MSC.tokenEmailMismatch', [], 'contao_default'),
            ]);
        }

        $form = $this->formFactory->create(UserLostPasswordType::class, $memberModel, ['reset_password' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $memberModel->password = $this->passwordHasher->hash($memberModel->password ?? '');
            $memberModel->tstamp = time();
            $memberModel->locked = 0;

            $memberModel->save();
            $optInToken->confirm();

            $this->addFlash(...Flash::confirmationModal()->headline(' Passwort-Reset erfolgreich')->text('Sie können sich nun mit Ihrem neuen Passwort anmelden.')->linkText('Zur Startseite')->create());

            return $this->renderForm('@FerienpassCore/Fragment/lost_password.html.twig', [
                'form' => $form,
            ]);
        }

        return $this->renderForm('@FerienpassCore/Fragment/lost_password.html.twig', [
            'form' => $form,
        ]);
    }
}
