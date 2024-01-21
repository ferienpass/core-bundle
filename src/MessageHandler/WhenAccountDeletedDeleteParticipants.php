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

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Message\AccountDelete;
use Ferienpass\CoreBundle\Message\ParticipantListChanged;
use Ferienpass\CoreBundle\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class WhenAccountDeletedDeleteParticipants
{
    public function __construct(private readonly MessageBusInterface $messageBus, private readonly UserRepository $repository, private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(AccountDelete $message)
    {
        /** @var User $user */
        $user = $this->repository->find($message->getUserId());
        if (null === $user) {
            return;
        }

        $offers = [];
        foreach ($user->getParticipants() as $participant) {
            foreach ($participant->getAttendances() as $attendance) {
                $offers[] = $attendance->getOffer()->getId();

                $this->em->remove($attendance);
            }

            $this->em->remove($participant);
        }

        $this->em->remove($user);
        $this->em->flush();

        foreach ($offers as $offerId) {
            $this->messageBus->dispatch(new ParticipantListChanged($offerId));
        }
    }
}
