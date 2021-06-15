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

namespace Ferienpass\CoreBundle\EventListener\Notification;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use NotificationCenter\Model\Gateway;
use NotificationCenter\Model\Message;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddNotificationTokens
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Hook("sendNotificationMessage")
     */
    public function __invoke(Message $message, array &$tokens, ?string $language, Gateway $gateway): bool
    {
        $tokens['copyright'] = $this->translator->trans('email.copyright', [], null, $language);

        if ($logo = '') {
            $tokens['logoSrc'] = sprintf('https://%s/%s', '', ltrim($logo, '/'));
        }

        return true;
    }
}
