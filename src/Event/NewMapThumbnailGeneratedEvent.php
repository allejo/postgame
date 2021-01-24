<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Event;

use allejo\bzflag\world\WorldDatabase;
use App\Entity\MapThumbnail;
use App\Entity\Replay;
use Symfony\Contracts\EventDispatcher\Event;

class NewMapThumbnailGeneratedEvent extends Event
{
    /** @var WorldDatabase */
    private $worldDatabase;

    /** @var MapThumbnail */
    private $mapThumbnail;

    /** @var Replay */
    private $replay;

    public function __construct(WorldDatabase $worldDatabase, MapThumbnail $mapThumbnail, Replay $replay)
    {
        $this->worldDatabase = $worldDatabase;
        $this->mapThumbnail = $mapThumbnail;
        $this->replay = $replay;
    }

    public function getReplay(): Replay
    {
        return $this->replay;
    }

    public function getWorldDatabase(): WorldDatabase
    {
        return $this->worldDatabase;
    }

    public function getMapThumbnail(): MapThumbnail
    {
        return $this->mapThumbnail;
    }
}
