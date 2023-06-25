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

namespace Ferienpass\CoreBundle\NotificationCenter\Gateway;

use Ferienpass\CoreBundle\NotificationCenter\MessageDraft\EmailMessageDraft;
use NotificationCenter\Model\Language;
use NotificationCenter\Model\Message;

/**
 * The gateway uses our customized EmailMessageDraft.
 * This gateway overrides the default e-mail gateway as it is defined so in the $GLOBALS[].
 */
class Email extends \NotificationCenter\Gateway\Email
{
    /**
     * @psalm-suppress UndefinedConstant
     */
    public function createDraft(Message $objMessage, array $arrTokens, $strLanguage = '')
    {
        if ('' === $strLanguage) {
            $strLanguage = (string) $GLOBALS['TL_LANGUAGE'];
        }

        if (null === ($languageModel = Language::findByMessageAndLanguageOrFallback($objMessage, $strLanguage))) {
            \System::log(sprintf('Could not find matching language or fallback for message ID "%s" and language "%s".', $objMessage->id, $strLanguage), __METHOD__, TL_ERROR);

            return null;
        }

        return new EmailMessageDraft($objMessage, $languageModel, $arrTokens);
    }
}
