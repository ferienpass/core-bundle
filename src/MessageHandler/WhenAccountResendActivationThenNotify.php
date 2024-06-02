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

use Ferienpass\CoreBundle\Entity\MessengerLog;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Message\AccountResendActivation;
use Ferienpass\CoreBundle\Notifier\Notifier;
use Ferienpass\CoreBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class WhenAccountResendActivationThenNotify
{
    public function __construct(private readonly Notifier $notifier, private readonly UserRepository $users, private readonly UrlGeneratorInterface $urlGenerator, private readonly UriSigner $uriSigner)
    {
    }

    public function __invoke(AccountResendActivation $message, MessengerLog $log): void
    {
        /** @var User $user */
        $user = $this->users->find($message->getUserId());
        if (null === $user) {
            return;
        }

        $notification = $this->notifier->accountCreated($user);
        if (null === $notification || '' === $email = (string) $user->getEmail()) {
            return;
        }

        $this->notifier->send(
            $notification->belongsTo($log)->actionUrl($this->uriSigner->sign($this->urlGenerator->generate('registration_activate', ['id' => $user->getId()], UrlGeneratorInterface::ABSOLUTE_URL))),
            new Recipient($email)
        );
    }
}
