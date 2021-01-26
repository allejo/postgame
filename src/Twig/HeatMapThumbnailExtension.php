<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Twig;

use App\Entity\PlayerHeatMap;
use App\Service\HeatMapWriterService;
use App\Twig\Exception\MissingExtensionException;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFilter;
use Twig\TwigFunction;

class HeatMapThumbnailExtension extends AbstractExtension
{
    /** @var Environment */
    private $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @throws MissingExtensionException when a required Twig extension could not
     *                                   be found in the current Twig environment
     */
    public function buildImgTag(PlayerHeatMap $heatmap): Markup
    {
        if (!$this->environment->hasExtension(AssetExtension::class)) {
            throw new MissingExtensionException('The `AssetExtension` is required for this extension');
        }

        /** @var AssetExtension $assetExtension */
        $assetExtension = $this->environment->getExtension(AssetExtension::class);
        $thumbnailURL = vsprintf('generated/%s/%s', [
            HeatMapWriterService::FOLDER_NAME,
            $heatmap->getFilename(),
        ]);

        $markup = <<<MARKUP
            <img
                class="heat-map-thumbnail"
                src="{$assetExtension->getAssetUrl($thumbnailURL)}"
                alt="A heatmap of {$heatmap->getPlayer()->getCallsign()}'s movement in this match"
                aria-hidden="true"
            />
MARKUP;

        return new Markup($markup, 'UTF-8');
    }

    public function getFilters()
    {
        return [
            new TwigFilter('heatmap_thumbnail', [$this, 'buildImgTag']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('heatmap_thumbnail', [$this, 'buildImgTag']),
        ];
    }
}
