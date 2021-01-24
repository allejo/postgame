<?php
declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Utility;

class PlayerMovementGrid
{
    /** @var array */
    private $movement;
    /** @var int Size of the map in the replay file */
    private $grid_size;
    /** @var int Size of the heatmap */
    private $heatmap_size;

    public function __construct(int $grid_size, int $heatmap_size)
    {
        $this->grid_size = $grid_size;
        $this->heatmap_size = $heatmap_size;
        $this->movement = array_fill(0, $heatmap_size, array_fill(0, $heatmap_size, 0));
    }

    /**
     * @return array
     */
    public function getMovement(): array
    {
        return $this->movement;
    }

    /**
     * @param array $movement
     * @return PlayerMovementGrid
     */
    public function setMovement(array $movement): PlayerMovementGrid
    {
        $this->movement = $movement;
        return $this;
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
     * @return PlayerMovementGrid
     */
    public function setGridSize(int $grid_size): PlayerMovementGrid
    {
        $this->grid_size = $grid_size;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeatmapSize(): int
    {
        return $this->heatmap_size;
    }

    /**
     * @param int $heatmap_size
     * @return PlayerMovementGrid
     */
    public function setHeatmapSize(int $heatmap_size): PlayerMovementGrid
    {
        $this->heatmap_size = $heatmap_size;
        return $this;
    }

    public function addPosition(float $x, float $y): void
    {
        //Shift to N
        $positive_x = $x + ($this->grid_size / 2);
        $positive_y = $y + ($this->grid_size / 2);

        $grid_quadrant_size = $this->grid_size / $this->heatmap_size;

        $grid_x = $this->heatmap_size - 1 - floor($positive_y / $grid_quadrant_size);
        $grid_y = floor($positive_x / $grid_quadrant_size);

        ++$this->movement[$grid_x][$grid_y];
    }
}
