<?php declare(strict_types=1);

require 'vendor/autoload.php';

use allejo\bzflag\replays\Replay;

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;

class GameMovement
{
    private $grid_size;
    private $heatmap_size;
    private $id_to_callsign;
    private $callsign_heatmap;


    /**
     * GameMovement constructor.
     * @param int $grid_size
     * @param int $heatmap_size
     */
    public function __construct(int $grid_size, int $heatmap_size)
    {

        $this->grid_size = $grid_size;
        $this->heatmap_size = $heatmap_size;
    }

    /**
     * @return int
     */
    public function getGridSize(): int
    {
        return $this->grid_size;
    }

    /**
     * @param int $grid_size
     */
    public function setGridSize(int $grid_size): void
    {
        $this->grid_size = $grid_size;
    }

    /**
     * @return array
     */
    public function getCallsignHeatmap(): array
    {
        return $this->callsign_heatmap;
    }

    /**
     * @param int $x
     * @param int $y
     */

}
