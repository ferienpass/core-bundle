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

namespace Ferienpass\CoreBundle\Controller\Backend;

use Contao\Model\Collection;
use Ferienpass\CoreBundle\Applications\UnconfirmedApplications;
use Ferienpass\CoreBundle\Message\ConfirmApplications;
use NotificationCenter\Model\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/benachrichtigungen")
 */
final class NotificationCenterController extends AbstractController
{
    private UnconfirmedApplications $unconfirmedApplications;

    public function __construct(UnconfirmedApplications $unconfirmedApplications)
    {
        $this->unconfirmedApplications = $unconfirmedApplications;
    }

    /**
     * @Route("", name="backend_notification_center")
     */
    public function main(): Response
    {
        /** @var Collection|Notification|null $notifications */
        $notifications = Notification::findAll();

        $data = (null !== $notifications) ? $notifications->fetchAll() : [];
        $types = array_column($data, 'type');
        $missing = array_diff($this->getMandatoryNotifications(), $types);

        return $this->render('@FerienpassCore/Backend/notification-center.html.twig', [
            'notifications' => $notifications,
            'missingNotifications' => $missing,
        ]);
    }

    /**
     * @Route("/zusagen-verschicken", name="backend_send_acceptances")
     */
    public function sendAcceptances(Request $request, MessageBusInterface $messageBus): Response
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $messageBus->dispatch(new ConfirmApplications($this->unconfirmedApplications->getAttendanceIds()));

            return $this->redirectToRoute('backend_send_acceptances');
        }

        return $this->render('@FerienpassCore/Backend/be_send_attendances_overview.html.twig', [
            'members' => $this->unconfirmedApplications->getUninformedMembers(),
            'participants' => $this->unconfirmedApplications->getUninformedParticipants(),
            'form' => $form->createView(),
        ]);
    }

    private function getMandatoryNotifications(): array
    {
        return [
            'attendance_changed_confirmed',
            'offer_cancelled',
            'offer_relaunched',
            'attendance_reminder',
            'host_invite_member',
            'member_activation',
            'member_registration',
        ];
    }
}
