<?php

declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\KnownMap;
use App\Entity\MapThumbnail;
use App\Entity\Replay;
use App\Service\ReplaySummaryService;
use App\Utility\QuickReplaySummary;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    /**
     * @Route("/maps", name="map_list")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $mapRepo = $em->getRepository(KnownMap::class);

        return $this->render('map/index.html.twig', [
            'maps' => $mapRepo->findAll(),
            'map_counts' => $mapRepo->findUsageCounts(),
            'map_thumbnails' => $mapRepo->findThumbnails(),
        ]);
    }

    /**
     * @Route("/maps/{map}/{slug}", name="map_show")
     */
    public function show(KnownMap $map, string $slug, ReplaySummaryService $summaryService)
    {
        $em = $this->getDoctrine()->getManager();
        $replayRepo = $em->getRepository(Replay::class);
        $replays = $replayRepo->findByMap($map, 14);
        $summaries = QuickReplaySummary::summarizeReplays($summaryService, $replays, null);

        return $this->render('map/show.html.twig', [
            'map' => $map,
            'thumbnail' => $em->getRepository(MapThumbnail::class)->findSingleThumbnailForMap($map),
            'match_count' => $em->getRepository(Replay::class)->getMapUsageCount($map),
            'replays' => $replays,
            'summaries' => $summaries,
        ]);
    }
}
