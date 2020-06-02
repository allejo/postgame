<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Service;

use allejo\bzflag\graphics\SVG\Radar\WorldRenderer;
use allejo\bzflag\replays\ReplayHeader;
use App\Entity\MapThumbnail;
use App\Entity\Replay;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class MapThumbnailWriterService implements IThumbnailWriter
{
    use ThumbnailWriterTrait;

    /** @var EntityManagerInterface */
    private $em;

    /** @var Filesystem */
    private $fs;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->fs = new Filesystem();
    }

    public function writeThumbnail(ReplayHeader $replayHeader, Replay $replay): bool
    {
        $thumbnails = $this->em->getRepository(MapThumbnail::class)->findBy([
            'worldHash' => $replayHeader->getWorld()->getWorldHash(),
        ]);

        if (count($thumbnails) >= 1) {
            return false;
        }

        $render = new WorldRenderer($replayHeader->getWorld());
        $svgOutput = $render->exportStringSVG();
        $svgFilename = $replayHeader->getWorld()->getWorldHash() . '.svg';

        $thumbnail = new MapThumbnail();
        $thumbnail->setWorldHash($replayHeader->getWorld()->getWorldHash());
        $thumbnail->setFilename($svgFilename);

        $replay->setMapThumbnail($thumbnail);

        $this->writeFile($svgFilename, $svgOutput);

        $this->em->persist($thumbnail);

        return true;
    }

    private function writeFile(string $filename, string $content): void
    {
        $this->fs->dumpFile($this->getFilePath($filename), $content);
    }

    private function getFilePath(string $filename): string
    {
        return sprintf('%s/map-thumbnails/%s', $this->targetDirectory, $filename);
    }
}
