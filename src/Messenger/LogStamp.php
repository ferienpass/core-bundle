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

namespace Ferienpass\CoreBundle\Messenger;

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\MessageLog;
use Ferienpass\CoreBundle\Message\LoggableMessageInterface;
use Symfony\Component\Messenger\Stamp\StampInterface;

class LogStamp implements StampInterface
{
    private MessageLog $messageLog;

    public function __construct(object $message, EntityManagerInterface $em)
    {
        $related = [];
        if ($message instanceof LoggableMessageInterface) {
            foreach ($message->getRelated() as $entity => $id) {
                $related[] = $em->getReference($entity, $id);
            }
        }

        $em->persist($this->messageLog = new MessageLog($message::class, related: $related));
    }

    public function getLogEntity(): MessageLog
    {
        return $this->messageLog;
    }
}
