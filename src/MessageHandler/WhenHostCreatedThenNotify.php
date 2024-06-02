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

namespace Ferienpass\CoreBundle\MessageHandler;

use Ferienpass\AdminBundle\Controller\Page\AccountsController;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\MessengerLog;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Message\HostCreated;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\HostRepository;
use Ferienpass\CoreBundle\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class WhenHostCreatedThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly HostRepository $hosts, private readonly UserRepository $user, private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(HostCreated $message, MessengerLog $log): void
    {
        /** @var User $user */
        $user = $this->user->find($message->getUserId());
        /** @var Host $host */
        $host = $this->hosts->find($message->getHostId());
        if (null === $user || null === $host) {
            return;
        }

        $notification = $this->notifier->hostCreated($host, $user);
        if (null === $notification || '' === $email = (string) $notification->getEmailTo()) {
            return;
        }

        $this->notifier->send(
            $notification->belongsTo($log)->actionUrl($this->urlGenerator->generate('admin_accounts_index', ['role' => array_search('ROLE_HOST', AccountsController::ROLES, true)], UrlGeneratorInterface::ABSOLUTE_URL)),
            new Recipient($email));
    }
}
