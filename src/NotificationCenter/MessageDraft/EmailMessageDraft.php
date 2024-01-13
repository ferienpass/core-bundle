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

namespace Ferienpass\CoreBundle\NotificationCenter\MessageDraft;

use Contao\Model;
use Contao\PageModel;
use Contao\System;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\EventListener\Notification\GetNotificationTokensTrait;
use Haste\Util\StringUtil;
use NotificationCenter\Model\Message;
use NotificationCenter\Model\Notification;

class EmailMessageDraft extends \NotificationCenter\MessageDraft\EmailMessageDraft
{
    use GetNotificationTokensTrait;

    /**
     * @psalm-suppress UndefinedConstant
     */
    public function getHtmlBody()
    {
        try {
            return $this->renderEmail();
        } catch (\Exception $e) {
            System::log($e->getMessage(), __METHOD__, TL_ERROR);

            // Sends text-only.
            return '';
        }
    }

    public function getAttachments()
    {
        if (!isset($this->arrTokens['attachment'])) {
            return [];
        }

        $attachments = [];

        if (is_file($this->arrTokens['attachment'])) {
            $attachments[] = $this->arrTokens['attachment'];
        }

        return $attachments;
    }

    public function getStringAttachments()
    {
        return [];
    }

    public function useExternalImages(): bool
    {
        return false;
    }

    public function getTextBodyRaw(): string
    {
        $text = $this->objLanguage->email_text;
        $text = StringUtil::recursiveReplaceTokensAndTags($text, $this->arrTokens, StringUtil::NO_TAGS);

        return $text;
    }

    private function renderEmail()
    {
        $twig = System::getContainer()->get('twig');
        if (null === $twig) {
            return '';
        }

        $tokens = $this->arrTokens;

        /**
         * @var Message&Model $message
         *
         * @psalm-suppress UndefinedDocblockClass
         */
        $message = $this->getMessage();
        /** @var Notification|Model $notification */
        $notification = $message->getRelated('pid');

        $rootPage = PageModel::findPublishedRootPages()[0];

        $parameters = array_merge($tokens, [
            'baseUrl' => \dirname((string) $rootPage->getAbsoluteUrl()),
            'email_text' => $this->getTextBodyRaw(),
        ]);

        $doctrine = System::getContainer()->get('doctrine');

        if (($tokens['participant'] ?? null)
            && ($participant = $doctrine->getRepository(Participant::class)->find($tokens['participant']))
            && ($tokens['offer'] ?? null)
            && ($offer = $doctrine->getRepository(Offer::class)->find($tokens['offer']))) {
            $tokens += self::getNotificationTokens($participant, $offer);

            $parameters = array_merge($tokens, $parameters);

            $parameters['offer'] = $offer;
            $parameters['participant'] = $participant;
        }

        $this->arrTokens = $tokens;

        return $twig->render(sprintf('@FerienpassCore/Email/%s.html.twig', $notification->type), $parameters);
    }
}
