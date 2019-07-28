<?php


namespace App\Controller;


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
     * @Route("/summarize/replays", name="api_summary_replays")
     */
    public function replayCountAction()
    {
        $em = $this->getDoctrine()->getManager();

        $summary_count = $em->getRepository(Replay::class)->getSummaryCount();

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
