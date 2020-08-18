<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\EventSubscriber;

use allejo\bzflag\world\WorldDatabase;
use App\Entity\KnownMap;
use App\Entity\MapThumbnail;
use App\Event\ExistingThumbnailUsedEvent;
use App\Event\NewMapThumbnailGeneratedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DucatiThumbnailMatcherSubscriber implements EventSubscriberInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onExistingThumbnailSelected(ExistingThumbnailUsedEvent $event): void
    {
        $thumbnail = $event->getMapThumbnail();

        if ($thumbnail && $thumbnail->getKnownMap() !== null) {
            return;
        }

        $this->applyDucatiClassification($thumbnail, $event->getWorldDatabase());
    }

    public function onNewThumbnailGeneratedSelected(NewMapThumbnailGeneratedEvent $event): void
    {
        $thumbnail = $event->getMapThumbnail();

        if (!$thumbnail) {
            return;
        }

        $this->applyDucatiClassification($thumbnail, $event->getWorldDatabase());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ExistingThumbnailUsedEvent::class => 'onExistingThumbnailSelected',
            NewMapThumbnailGeneratedEvent::class => 'onNewThumbnailGeneratedSelected',
        ];
    }

    private function applyDucatiClassification(MapThumbnail $thumbnail, WorldDatabase $worldDatabase): void
    {
        $worldSize = (int)$worldDatabase->getBZDBManager()->getBZDBVariable('_worldSize');

        if ($worldSize === 600) {
            if (($map = $this->getDucatiMini()) !== null) {
                $thumbnail->setKnownMap($map);
            }
        } elseif ($worldSize === 800) {
            if (($map = $this->getDucati()) !== null) {
                $thumbnail->setKnownMap($map);
            }
        }
    }

    private function getDucati(): ?KnownMap
    {
        return $this->entityManager
            ->getRepository(KnownMap::class)
            ->findOneBy([
                'name' => 'Ducati',
            ])
        ;
    }

    private function getDucatiMini(): ?KnownMap
    {
        return $this->entityManager
            ->getRepository(KnownMap::class)
            ->findOneBy([
                'name' => 'Ducati Mini',
            ])
        ;
    }
}
