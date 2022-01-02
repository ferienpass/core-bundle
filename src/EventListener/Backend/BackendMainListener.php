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

namespace Ferienpass\CoreBundle\EventListener\Backend;

use Composer\InstalledVersions;
use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Template;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;
use Twig\Environment as TwigEnvironment;

class BackendMainListener implements ServiceAnnotationInterface
{
    private TwigEnvironment $twig;
    private ContaoFramework $framework;

    public function __construct(TwigEnvironment $twig, ContaoFramework $framework)
    {
        $this->twig = $twig;
        $this->framework = $framework;
    }

    /**
     * @Hook("parseTemplate")
     */
    public function __invoke(Template $template): void
    {
        if ('be_main' !== $template->getName()) {
            return;
        }

        /** @var BackendUser $user */
        $user = $this->framework->createInstance(BackendUser::class);
        if (!$user->id) {
            return;
        }

        $template->headerProfile = $this->twig->render('@FerienpassCore/Backend/header-profile.html.twig', [
            'userInitials' => $this->getUserInitials($user),
        ]);
        $template->version = $this->getVersion();
    }

    private function getUserInitials(BackendUser $user): string
    {
        $parts = explode(' ', $user->name, 2);
        $parts = array_map(static fn (string $part) => $part[0], $parts);

        return implode('', $parts);
    }

    private function getVersion(): ?string
    {
        foreach (['ferienpass/base', 'ferienpass/ferienpass'] as $package) {
            if (!InstalledVersions::isInstalled($package)) {
                continue;
            }

            return InstalledVersions::getPrettyVersion($package);
        }

        return null;
    }
}
