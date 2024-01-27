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

use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Message\HostCreated;
use Ferienpass\CoreBundle\Notifier;
use Ferienpass\CoreBundle\Repository\HostRepository;
use Ferienpass\CoreBundle\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;

#[AsMessageHandler]
class WhenHostCreatedThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly HostRepository $hostRepository, private readonly UserRepository $userRepository)
    {
    }

    public function __invoke(HostCreated $message): void
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

        $this->notifier->send($notification, new Recipient($email, (string) $user->getMobile()));
    }
}
