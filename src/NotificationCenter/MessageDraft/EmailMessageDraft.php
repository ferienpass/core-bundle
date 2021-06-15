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
use Contao\System;
use Haste\Util\StringUtil;
use NotificationCenter\Model\Message;
use NotificationCenter\Model\Notification;

class EmailMessageDraft extends \NotificationCenter\MessageDraft\EmailMessageDraft
{
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

        /**
         * @var Message&Model $message
         * @psalm-suppress UndefinedDocblockClass
         */
        $message = $this->getMessage();
        /** @var Notification|Model $notification */
        $notification = $message->getRelated('pid');

        $parameters = array_merge($this->arrTokens, ['email_text' => $this->getTextBodyRaw()]);

        return $twig->render(sprintf('@FerienpassCore/Email/%s.html.twig', $notification->type), $parameters);
    }
}
