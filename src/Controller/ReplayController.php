<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Replay;
use App\Service\ReplaySummaryService;
use App\Utility\DefaultArray;
use App\Utility\QuickReplaySummary;
use App\Utility\UnsummarizedException;
use App\Utility\WrongSummarizationException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class ReplayController extends AbstractController
{
    /**
     * @Route("/replays", name="replay_list")
     *
     * @param Request              $request
     * @param ReplaySummaryService $summaryService
     * @param LoggerInterface      $logger
     *
     * @return Response
     */
    public function list(Request $request, ReplaySummaryService $summaryService, LoggerInterface $logger): Response
    {
        $timestamp = $request->get('after');

        $em = $this->getDoctrine()->getManager();
        $replays = $em->getRepository(Replay::class)->findByTimeRange($timestamp);

        /** @var array<string, Replay[]> $replaysByDay */
        $replaysByDay = new DefaultArray([]);

        /** @var QuickReplaySummary[] $summaries */
        $summaries = [];

        foreach ($replays as $replay) {
            $replaysByDay[$replay->getStartTime()->format('Y-m-d')][] = $replay;

            $summaryService->summarizeQuick($replay);

            try {
                $summary = new QuickReplaySummary();
                $summary->duration = $summaryService->getDuration();
                $summary->winner = $summaryService->getWinner();
                $summary->winnerScore = $summaryService->getWinnerScore();
                $summary->loser = $summaryService->getLoser();
                $summary->loserScore = $summaryService->getLoserScore();

                $summaries[$replay->getId()] = $summary;
            } catch (UnsummarizedException | WrongSummarizationException $e) {
                $logger->warning('Replay ID {id} could not be summarized correctly', [
                    'id' => $replay->getId(),
                ]);
            }
        }

        return $this->render('replay/index.html.twig', [
            'replays' => $replaysByDay,
            'summaries' => $summaries,
        ]);
    }

    /**
     * @Route(
     *     "/replays/{id}/{filename}/{_format}",
     *     name="replay_show",
     *     defaults={
     *         "_format": "html"
     *     }
     * )
     *
     * @param int                  $id
     * @param string               $filename
     * @param string               $_format
     * @param ReplaySummaryService $summaryService
     * @param LoggerInterface      $logger
     *
     * @return Response
     */
    public function show(int $id, string $filename, string $_format, ReplaySummaryService $summaryService, LoggerInterface $logger): Response
    {
        $em = $this->getDoctrine()->getManager();
        $replay = $em->getRepository(Replay::class)->findOneBy([
            'id' => $id,
            'fileName' => $filename,
        ]);

        if ($replay === null) {
            throw $this->createNotFoundException();
        }

        $summaryService->summarizeFull($replay);

        try {
            $replaySummary = [
                'id' => $replay->getId(),
                'filename' => $replay->getFileName(),
                'start' => $replay->getStartTime(),
                'end' => $replay->getEndTime(),
                'duration' => $summaryService->getDuration(),
                'winner' => $summaryService->getWinner(),
                'winner_score' => $summaryService->getWinnerScore(),
                'loser' => $summaryService->getLoser(),
                'loser_score' => $summaryService->getLoserScore(),
                'players' => $summaryService->getPlayerRecords(),
                'flag_caps' => $summaryService->getFlagCaps(),
                'messages' => $summaryService->getChatMessages(),
            ];
        } catch (UnsummarizedException | WrongSummarizationException $e) {
            $logger->warning($e->getMessage());

            throw new HttpException(500, 'Replay summarizing failed');
        }

        if ($_format === 'json') {
            $replaySummary['start'] = $replaySummary['start']->format(DATE_ATOM);
            $replaySummary['end'] = $replaySummary['end']->format(DATE_ATOM);

            return $this->json($replaySummary);
        }

        return $this->render('replay/show.html.twig', $replaySummary);
    }
}
