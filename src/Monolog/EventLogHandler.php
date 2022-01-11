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

namespace Ferienpass\CoreBundle\Monolog;

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\EventLog;
use Ferienpass\CoreBundle\Entity\EventLogRelated;
use Ferienpass\CoreBundle\Entity\NotificationLog;
use Ferienpass\CoreBundle\Monolog\Context\MessageContext;
use Ferienpass\CoreBundle\Monolog\Context\NotificationContext;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * When a log record holds a MessageContext or NotificationContext, persist the log in the database.
 */
class EventLogHandler extends AbstractProcessingHandler
{
    public function __construct(protected EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function write(array $record): void
    {
        $messageContext = $record['context']['message'] ?? null;
        if (!$messageContext instanceof MessageContext) {
            return;
        }

        $message = $messageContext->getMessage();

        $id = $record['context']['id'];
        if (null === $logEntry = $this->em->getRepository(EventLog::class)->findOneBy(['uniqueId' => $id])) {
            $logEntry = new EventLog($id, $message::class);

            // Flush to get id
            $this->em->persist($logEntry);
            $this->em->flush();

            foreach ($message->getRelated() as $relatedTable => $relatedIds) {
                foreach ((array) $relatedIds as $relatedId) {
                    $relatedEntry = new EventLogRelated($logEntry, $relatedTable, $relatedId);

                    $this->em->persist($relatedEntry);
                }
            }
        }

        if (isset($record['context']['notification'])
            && ($context = $record['context']['notification'])
            && $context instanceof NotificationContext
            && $context->isSuccessful()) {
            $notificationLog = new NotificationLog(
                $logEntry,
                $context->getNotification(),
                $context->getMessage(),
                $context->getTokens(),
                $context->getLanguage()
            );

            $this->em->persist($notificationLog);
        }

        $this->em->flush();
    }
}
