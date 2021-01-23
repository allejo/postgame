<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Twig;

use App\Entity\MapThumbnail;
use App\Service\MapThumbnailWriterService;
use App\Twig\Exception\MissingExtensionException;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MapThumbnailExtension extends AbstractExtension
{
    /** @var Environment */
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @throws MissingExtensionException when a required Twig extension could
     *                                   not be found in the current Twig environment
     */
    public function buildImgTag(MapThumbnail $thumbnail): Markup
    {
        $mapName = 'a map';
        if ($thumbnail->getKnownMap()) {
            $mapName = $thumbnail->getKnownMap()->getName();
        }

        if (!$this->environment->hasExtension(AssetExtension::class)) {
            throw new MissingExtensionException('The `AssetExtension` is required for this extension');
        }

        /** @var AssetExtension $assetExtension */
        $assetExtension = $this->environment->getExtension(AssetExtension::class);
        $thumbnailURL = vsprintf('generated/%s/%s.svg', [
            MapThumbnailWriterService::FOLDER_NAME,
            $thumbnail->getWorldHash(),
        ]);

        $markup = <<<MARKUP
            <img
                class="map-thumbnail"
                src="{$assetExtension->getAssetUrl($thumbnailURL)}"
                alt="A bird's eye view of {$mapName}"
                aria-hidden="true"
            />
MARKUP;

        return new Markup($markup, 'UTF-8');
    }

    public function getFilters()
    {
        return [
            new TwigFilter('map_thumbnail', [$this, 'buildImgTag']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('map_thumbnail', [$this, 'buildImgTag']),
        ];
    }
}
