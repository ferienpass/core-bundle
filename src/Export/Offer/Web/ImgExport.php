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

namespace Ferienpass\CoreBundle\Export\Offer\Web;

use Ferienpass\CmsBundle\Controller\Fragment\OfferDetailsController;
use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Export\Offer\OfferExportInterface;
use Knp\Snappy\Image as SnappyImage;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class ImgExport implements OfferExportInterface
{
    public function __construct(private readonly HttpKernelInterface $httpKernel, private readonly Filesystem $filesystem, private readonly SnappyImage $snappyImage)
    {
    }

    public function generate(Offer $offer, string $destination = null): string
    {
        $hash = md5(sprintf('%s-%s', $offer->getId(), $offer->getModifiedAt()->format('c')));

        $imgPath = sprintf('%s/web/%s.jpg', sys_get_temp_dir(), $hash);
        if (!file_exists($imgPath)) {
            $this->snappyImage->generateFromHtml($this->getHtml($offer), $imgPath, [], true);
        }

        if (null !== $destination) {
            $this->filesystem->copy($imgPath, $destination);
        }

        return $destination ?? $imgPath;
    }

    private function getHtml(Offer $offer): string
    {
        $request = new Request();

        $request->attributes->set('_controller', OfferDetailsController::class);
        $request->attributes->set('offer', $offer);

        return (string) $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST)->getContent();
    }
}
