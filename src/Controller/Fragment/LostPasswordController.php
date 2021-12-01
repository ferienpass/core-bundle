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
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Ferienpass\CoreBundle\Form\UserLostPasswordType;
use Ferienpass\CoreBundle\Ux\Flash;
use NotificationCenter\Model\Notification;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Translation\TranslatableMessage;

class LostPasswordController extends AbstractController
{
    private LoggerInterface $logger;
    private OptInInterface  $optIn;

    public function __construct(LoggerInterface $logger, OptInInterface $optIn)
    {
        $this->logger = $logger;
        $this->optIn = $optIn;
    }

    public function __invoke(Request $request): Response
    {
        if ($request->query->has('token') && ($token = (string) $request->query->get('token'))
            && 0 === strncmp($token, 'pw-', 3)) {
            return $this->setNewPassword($request);
        }

        $form = $this->createForm(UserLostPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $memberModel = MemberModel::findActiveByEmailAndUsername($data['email']);
            if (null === $memberModel) {
                sleep(2); // Wait 2 seconds while brute forcing :)
                $form->addError(new FormError($GLOBALS['TL_LANG']['MSC']['accountNotFound']));
            } else {
                return $this->sendPasswordLink($memberModel);
            }
        }

        return $this->render('@FerienpassCore/Fragment/lost_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public static function getSubscribedServices()
    {
        $services = parent::getSubscribedServices();

        $services['security.encoder_factory'] = EncoderFactoryInterface::class;

        return $services;
    }

    protected function sendPasswordLink(MemberModel $memberModel): Response
    {
        $notification = Notification::findOneBy('type', 'member_password');
        if (null === $notification) {
            throw new \RuntimeException('No notification for password reset found!');
        }

        $optInToken = $this->optIn->create('pw', $memberModel->email, ['tl_member' => [$memberModel->id]]);

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
            || 1 !== \count($arrIds = current($related))
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

        $form = $this->createForm(UserLostPasswordType::class, $memberModel, ['reset_password' => true]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->get('security.encoder_factory')->getEncoder(FrontendUser::class);

            $memberModel->password = $encoder->encodePassword($memberModel->password, null);
            $memberModel->tstamp = time();
            $memberModel->locked = 0;

            $memberModel->save();
            $optInToken->confirm();

            $this->addFlash(...Flash::confirmationModal()->headline(' Passwort-Reset erfolgreich')->text('Sie können sich nun mit Ihrem neuen Passwort anmelden.')->linkText('Zur Startseite')->create());

            return $this->render('@FerienpassCore/Fragment/lost_password.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        return $this->render('@FerienpassCore/Fragment/lost_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
