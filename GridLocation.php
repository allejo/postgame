<?php declare(strict_types=1);

require 'vendor/autoload.php';

use allejo\bzflag\replays\Replay;

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;

class GameMovement
{
    /**
     * Size of the map in the replay file
     */
    private $grid_size;
    /**
     * Size of the heatmap
     */
    private $heatmap_size;
    /**
     * Hashmap with {userID: Callsign} since userID is temporary
     */
    private $id_to_callsign;
    /**
     * Hashmap with {Callsign: Heatmap}
     */
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
     * @return int
     */
    public function getHeatmapSize(): int
    {
        return $this->heatmap_size;
    }

    /**
     * @return array
     */
    public function getCallsignHeatmap(): array
    {
        return $this->callsign_heatmap;
    }

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

    /**
     * Import and Process a replay file
     * @param string $location
     * @throws \allejo\bzflag\networking\Packets\PacketInvalidException
     * @throws \allejo\bzflag\replays\Exceptions\InvalidReplayException
     * @throws \allejo\bzflag\world\Exceptions\InvalidWorldCompressionException
     * @throws \allejo\bzflag\world\Exceptions\InvalidWorldDatabaseException
     */
    public function replayHeatmap(string $location): void
    {
        $replay = new Replay($location);
        $this->id_to_callsign = array();
        $this->callsign_heatmap = array();

        foreach ($replay->getPacketsIterable() as $packet) {
            if ($packet->getPacketType() === "MsgAddPlayer") {

                if (!isset( $this->callsign_heatmap[$packet->getCallsign()])) {
                    $this->callsign_heatmap[$packet->getCallsign()] = array_fill(0, $this->heatmap_size, array_fill(0, $this->heatmap_size, 0));
                }

                $this->id_to_callsign[$packet->getPlayerIndex()] = $packet->getCallsign();
            }


            if ($packet->getPacketType() === "MsgPlayerUpdate") {
                $callsign = $this->id_to_callsign[$packet->getPlayerId()];
                $position = $packet->getState()->position;
                $this->addPosition($position[0], $position[1], $callsign);

            }
            if ($packet->getPacketType() === "MsgRemovePlayer") {
                unset($this->id_to_callsign[$packet->getPlayerId()]);
            }
        }
    }
}
