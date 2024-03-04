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
use App\Entity\Replay;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class ApiV1Controller extends AbstractController
{
    /**
     * @Route("/summarize/replays/{map}", name="api_summary_replays")
     */
    public function replayCountAction(?KnownMap $map = null)
    {
        $summary_count = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Replay::class)
            ->getSummaryCount(null, null, $map)
        ;

        return new JsonResponse($this->renderTimeSeriesGraph(
            'match_date',
            ['match_count'],
            $summary_count
        ));
    }

    private function renderTimeSeriesGraph(string $xAxisColumn, array $lines, array $data)
    {
        return [
            'type' => 'timeseries',
            'x-axis' => $xAxisColumn,
            'lines' => $lines,
            'data' => $data,
        ];
    }
}
