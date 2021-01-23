<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\PlayerHeatMap;
use App\Entity\Replay;
use Symfony\Component\Filesystem\Filesystem;
use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;


class HeatMapWriterService
{

    public const FOLDER_NAME = 'heat-maps';

    /** @var Filesystem */
    private $fs;


    const GRADIENT_FIRST = "#1a2a6c";

    const GRADIENT_SECOND = "#b21f1f";

    const GRADIENT_THIRD =  "#fdbb2d";



    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    public function writeHeatMap(Replay $replay, PlayerHeatMap $heatMap, int $SVGSize,
                                 string $GradientStart=self::GRADIENT_FIRST,
                                 string $GradientMid=self::GRADIENT_SECOND,
                                 string $GradientEnd=self::GRADIENT_THIRD): bool
    {

        $heatmap = $heatMap->getHeatmap();
        $heatmap_size = count($heatmap);
        $oldRange = $this->maxval($heatmap);
        $image = new SVG($SVGSize, $SVGSize);
        $doc = $image->getDocument();

        $SquareSize = $SVGSize/$heatmap_size;
        for ($i = 0; $i < count($heatmap); $i++) {
            for ($j = 0; $j < count($heatmap[$i]); $j++) {
                $square = new SVGRect($SquareSize*$j, $SquareSize*$i, $SquareSize, $SquareSize);
                $colour = $this->gradient($heatmap[$i][$j]/$oldRange, $GradientStart, $GradientMid, $GradientEnd);

                $square->setStyle('fill', $colour);
                $doc->addChild($square);
            }
        }
        $svgFilename = $database->getWorldHash() . '.svg';

        $this->writeFile($svgFilename, $image);
        return true;
    }

    /**
     * Get the max value in a 2D array
     * @param array $x
     * @return int
     */
    function maxval(array $x): int
    {
        $max = 0;
        foreach ($x as $row){
            $max_col = max($row);
            if ($max_col>$max){
                $max = $max_col;
            }
        }
        return $max;
    }

    /**
     * Returns a colour value for a given heatmap value
     * @param $t float
     * @param $start string
     * @param $middle string
     * @param $end string
     * @return string
     */
    function gradient(float $t,string $start, string $middle, string $end) {
        return $t>=0.5 ? $this->linear($middle,$end,($t-.5)*2) : $this->linear($start,$middle,$t*2);
    }

    /** Linearly Interpolate a given value over a given range
     * @param $start string
     * @param $end string
     * @param $x float
     * @return string
     */
    function linear(string $start, string $end, float $x) {
        $r = $this->byteLinear($start[1].$start[2], $end[1].$end[2], $x);
        $g = $this->byteLinear($start[3].$start[4], $end[3].$end[4], $x);
        $b = $this->byteLinear($start[5].$start[6], $end[5].$end[6], $x);
        return "#".$r.$g.$b;
    }

    function byteLinear(string $a, string $b, float $x) {
        $y = (hexdec(('0x'.$a))*(1-$x) + hexdec(('0x'.$b))*$x)|0;
        return dechex($y);
    }


    private function writeFile(string $filename, SVG $content): void
    {
        $this->fs->dumpFile($this->getFilePath($filename), $content);
    }

    private function getFilePath(string $filename): string
    {
        return sprintf('%s/%s/%s', $this->targetDirectory, self::FOLDER_NAME, $filename);
    }
}
