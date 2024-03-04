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
    /** @var int[][] The heatmap 2D array */
    private $movement;
    /** @var int Size of the map in the replay file in World Units (wu) */
    private $worldSize;
    /** @var int The number of cells in a single row of the heatmaps */
    private $heatmapSize;

    public function __construct(int $worldSize, int $heatmapSize)
    {
        $this->worldSize = $worldSize;
        $this->heatmapSize = $heatmapSize;
        $this->movement = array_fill(0, $heatmapSize, array_fill(0, $heatmapSize, 0));
    }

    public function getMovement(): array
    {
        return $this->movement;
    }

    public function setMovement(array $movement): PlayerMovementGrid
    {
        $this->movement = $movement;

        return $this;
    }

    public function getWorldSize(): int
    {
        return $this->worldSize;
    }

    public function setWorldSize(int $worldSize): PlayerMovementGrid
    {
        $this->worldSize = $worldSize;

        return $this;
    }

    public function getHeatmapSize(): int
    {
        return $this->heatmapSize;
    }

    public function setHeatmapSize(int $heatmapSize): PlayerMovementGrid
    {
        $this->heatmapSize = $heatmapSize;

        return $this;
    }

    public function addPosition(float $x, float $y): void
    {
        //Shift to N
        $positive_x = $x + ($this->worldSize / 2);
        $positive_y = $y + ($this->worldSize / 2);

        $cellSize = $this->worldSize / $this->heatmapSize;

        $grid_x = $this->heatmapSize - 1 - floor($positive_y / $cellSize);
        $grid_y = floor($positive_x / $cellSize);

        ++$this->movement[$grid_x][$grid_y];
    }
}
