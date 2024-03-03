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
use Ferienpass\CoreBundle\Entity\MessageLog;
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
    public function __construct(private readonly Notifier $notifier, private readonly HostRepository $hostRepository, private readonly UserRepository $userRepository, private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(HostCreated $message, MessageLog $log): void
    {
        /** @var User $user */
        $user = $this->userRepository->find($message->getUserId());
        /** @var Host $host */
        $host = $this->hostRepository->find($message->getHostId());
        if (null === $user || null === $host) {
            return;
        }

        $notification = $this->notifier->hostCreated($host, $user);
        if (null === $notification || '' === $email = (string) $user->getEmail()) {
            return;
        }

        $this->notifier->send(
            $notification->belongsTo($log)->actionUrl($this->urlGenerator->generate('admin_accounts_index', ['role' => array_search('ROLE_HOST', AccountsController::ROLES, true)], UrlGeneratorInterface::ABSOLUTE_URL)),
            new Recipient($email, (string) $user->getMobile()));
    }
}
