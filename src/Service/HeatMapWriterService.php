<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\PlayerHeatMap;
use SVG\Nodes\Shapes\SVGRect;
use SVG\SVG;
use Symfony\Component\Filesystem\Filesystem;

class HeatMapWriterService implements IFileWriter
{
    use FileWriterTrait;

    public const FOLDER_NAME = 'heat-maps';

    public const GRADIENT_FIRST = '#1a2a6c';

    public const GRADIENT_SECOND = '#b21f1f';

    public const GRADIENT_THIRD = '#fdbb2d';

    /** @var Filesystem */
    private $fs;

    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    /**
     * Create and write heatmap to a file location.
     *
     * @param PlayerHeatMap $heatMap       Heatmap 2D array
     * @param int           $svgSize       size of heatMap SVG
     * @param string        $gradientStart Beginning colour for gradient
     * @param string        $gradientMid   Mid colour for gradient
     * @param string        $gradientEnd   End colour for gradient
     */
    public function writeHeatMap(
        PlayerHeatMap $heatMap,
        int $svgSize,
        string $gradientStart = self::GRADIENT_FIRST,
        string $gradientMid = self::GRADIENT_SECOND,
        string $gradientEnd = self::GRADIENT_THIRD
    ): bool {
        $heatmap = $heatMap->getHeatmap();
        $heatmapSize = count($heatmap);
        $oldRange = $this->maxval($heatmap);
        $image = new SVG($svgSize, $svgSize);
        $doc = $image->getDocument();

        $squareSize = $svgSize / $heatmapSize;
        for ($i = 0, $iMax = count($heatmap); $i < $iMax; ++$i) {
            for ($j = 0, $jMax = count($heatmap[$i]); $j < $jMax; ++$j) {
                $square = new SVGRect($squareSize * $j, $squareSize * $i, $squareSize, $squareSize);
                $colour = $this->gradient($heatmap[$i][$j] / $oldRange, $gradientStart, $gradientMid, $gradientEnd);

                $square->setStyle('fill', $colour);
                $doc->addChild($square);
            }
        }

        $this->writeFile($heatMap->getFilename(), $image->toXMLString());

        return true;
    }

    /**
     * Get the max value in a 2D array.
     */
    private function maxval(array $x): int
    {
        $max = 0;
        foreach ($x as $row) {
            $max_col = max($row);
            if ($max_col > $max) {
                $max = $max_col;
            }
        }

        return $max;
    }

    /**
     * Returns a colour value for a given heatmap value.
     *
     * @param float  $offset The point in the gradient that we're trying to find a colour value for
     * @param string $start  Colour at the beginning of the gradient in hex
     * @param string $middle Colour at the middle of the gradient in hex
     * @param string $end    Colour at the end of the gradient in hex
     *
     * @return string The colour value at offset in the gradient
     */
    private function gradient(float $offset, string $start, string $middle, string $end): string
    {
        return $offset >= 0.5 ? $this->linear($middle, $end, ($offset - .5) * 2)
            : $this->linear($start, $middle, $offset * 2);
    }

    /**
     * Linearly Interpolate a given value over a given range.
     *
     * @param string $start Start value, in hex, of the range on which we want to interpolate
     * @param string $end   End value, in hex, of the range on which we want to interpolate
     * @param $x float The value that we want to interpolate
     *
     * @return string Interpolated value in a hex string form
     */
    private function linear(string $start, string $end, float $x): string
    {
        $r = $this->byteLinear($start[1] . $start[2], $end[1] . $end[2], $x);
        $g = $this->byteLinear($start[3] . $start[4], $end[3] . $end[4], $x);
        $b = $this->byteLinear($start[5] . $start[6], $end[5] . $end[6], $x);

        return '#' . $r . $g . $b;
    }

    private function byteLinear(string $a, string $b, float $x): string
    {
        $y = (hexdec(('0x' . $a)) * (1 - $x) + hexdec(('0x' . $b)) * $x) | 0;

        return dechex($y);
    }
}
