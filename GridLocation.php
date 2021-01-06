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

    /**
     * Add A position from the replay file to the heatmap
     * @param float $x
     * @param float $y
     * @param string $callsign
     */
    public function addPosition(float $x, float $y, string $callsign): void
    {
        //Shift to N
        $positive_x = $x + ($this->grid_size / 2);
        $positive_y = $y + ($this->grid_size / 2);

        $grid_quadrant_size = $this->grid_size / $this->heatmap_size;

        $grid_x = $this->heatmap_size - 1 - floor($positive_y / $grid_quadrant_size);
        $grid_y = floor($positive_x / $grid_quadrant_size);

        ($this->callsign_heatmap[$callsign])[$grid_x][$grid_y]++;
    }
}
