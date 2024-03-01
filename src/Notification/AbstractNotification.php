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

namespace Ferienpass\CoreBundle\Notification;

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\Attendance;
use Ferienpass\CoreBundle\Entity\Host;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Entity\Payment;
use Ferienpass\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Contracts\Service\Attribute\SubscribedService;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberTrait;
use Twig\Environment;
use Twig\Error\Error;

abstract class AbstractNotification extends Notification implements ServiceSubscriberInterface
{
    use ServiceSubscriberTrait;

    private ?string $replyTo = null;

    public function replyTo(string $replyTo): static
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getSubject(): string
    {
        try {
            return $this->twig()->createTemplate(parent::getSubject())->render($this->getContext());
        } catch (Error) {
            return parent::getSubject();
        }
    }

    public function getContent(): string
    {
        try {
            return $this->twig()->createTemplate(parent::getContent())->render($this->getContext());
        } catch (Error) {
            return parent::getContent();
        }
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    /**
     * The context is used to replace placeholders in texts and templates.
     */
    public function getContext(): array
    {
        return [
            'baseUrl' => ($this->requestStack()->getCurrentRequest()?->getSchemeAndHttpHost() ?? '').($this->requestStack()->getCurrentRequest()?->getBaseUrl() ?? ''),
        ];
    }

    public static function getAvailableTokens(): array
    {
        return ['baseUrl'];
    }

    public function createMock(): static
    {
        $notification = clone $this;

        if (\in_array('attendances', $notification::getAvailableTokens(), true) && method_exists($notification, 'attendance')) {
            foreach ($this->doctrine()->getRepository(Participant::class)->createQueryBuilder('i')->getQuery()->setMaxResults(1)->getOneOrNullResult()->getAttendances() as $attendance) {
                $notification->attendance($attendance);
            }
        } elseif (method_exists($notification, 'attendance')) {
            $notification->attendance($this->doctrine()->getRepository(Attendance::class)->createQueryBuilder('i')->where('i.participant IS NOT NULL')->getQuery()->setMaxResults(1)->getOneOrNullResult());
        }
        if (method_exists($notification, 'user')) {
            $notification->user($this->doctrine()->getRepository(User::class)->findOneBy([]));
        }
        if (method_exists($notification, 'payment')) {
            $notification->payment($this->doctrine()->getRepository(Payment::class)->findOneBy([]));
        }
        if (method_exists($notification, 'host')) {
            $notification->host($this->doctrine()->getRepository(Host::class)->findOneBy([]));
        }
        if (method_exists($notification, 'token')) {
            $notification->token('example');
        }
        if (method_exists($notification, 'email')) {
            $notification->email('example@example.org');
        }

        return $notification;
    }

    #[SubscribedService]
    private function doctrine(): EntityManagerInterface
    {
        return $this->container->get(__METHOD__);
    }

    #[SubscribedService]
    private function requestStack(): RequestStack
    {
        return $this->container->get(__METHOD__);
    }

    #[SubscribedService]
    private function twig(): Environment
    {
        return $this->container->get(__METHOD__);
    }
}
