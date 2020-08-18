<?php declare(strict_types=1);

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

class ExistingThumbnailUsedEvent extends Event
{
    /** @var WorldDatabase */
    private $worldDatabase;

    /** @var MapThumbnail */
    private $mapThumbnail;

    /** @var Replay */
    private $replay;

    /** @var bool */
    private $causedByRegeneration;

    public function __construct(WorldDatabase $worldDatabase, MapThumbnail $mapThumbnail, Replay $replay, bool $causedByRegeneration)
    {
        $this->worldDatabase = $worldDatabase;
        $this->mapThumbnail = $mapThumbnail;
        $this->replay = $replay;
        $this->causedByRegeneration = $causedByRegeneration;
    }

    public function isCausedByRegeneration(): bool
    {
        return $this->causedByRegeneration;
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
