<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

use allejo\bzflag\graphics\SVG\Radar\WorldRenderer;
use allejo\bzflag\replays\ReplayHeader;
use allejo\bzflag\world\WorldDatabase;
use App\Entity\MapThumbnail;
use App\Entity\Replay;
use App\Event\ExistingThumbnailUsedEvent;
use App\Event\NewMapThumbnailGeneratedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

class MapThumbnailWriterService implements IFileWriter
{
    use FileWriterTrait;

    public const FOLDER_NAME = 'map-thumbnails';

    /** @var EntityManagerInterface */
    private $em;

    /** @var Filesystem */
    private $fs;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $entityManager;
        $this->fs = new Filesystem();
        $this->eventDispatcher = $eventDispatcher;
    }

    public function writeThumbnail(ReplayHeader $replayHeader, Replay $replay, bool $regenerate): bool
    {
        $worldDatabase = $replayHeader->getWorldDatabase();

        /** @var null|MapThumbnail $existingThumbnail */
        $existingThumbnail = $this->em->getRepository(MapThumbnail::class)->findOneBy([
            'worldHash' => $worldDatabase->getWorldHash(),
        ]);

        if ($existingThumbnail !== null) {
            $replay->setMapThumbnail($existingThumbnail);

            $event = new ExistingThumbnailUsedEvent($worldDatabase, $existingThumbnail, $replay, $regenerate);
            $this->eventDispatcher->dispatch($event);

            if ($regenerate) {
                $this->writeThumbnailToFilesystem($worldDatabase);
            }

            return true;
        }

        $thumbnail = new MapThumbnail();
        $thumbnail->setWorldHash($worldDatabase->getWorldHash());

        $replay->setMapThumbnail($thumbnail);
        $this->writeThumbnailToFilesystem($worldDatabase);

        $event = new NewMapThumbnailGeneratedEvent($worldDatabase, $thumbnail, $replay);
        $this->eventDispatcher->dispatch($event);

        $this->em->persist($thumbnail);

        return true;
    }

    private function writeThumbnailToFilesystem(WorldDatabase $database): void
    {
        $render = new WorldRenderer($database);
        $svgOutput = $render->exportStringSVG();
        $svgFilename = $database->getWorldHash() . '.svg';

        $this->writeFile($svgFilename, $svgOutput);
    }
}
