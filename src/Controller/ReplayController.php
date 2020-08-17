<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\Replay;
use App\Repository\ReplayRepository;
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
     */
    public function listAction(Request $request, ReplaySummaryService $summaryService, LoggerInterface $logger): Response
    {
        $after = $this->safeGetTimestamp($request, 'after');
        $before = $this->safeGetTimestamp($request, 'before');

        /** @var ReplayRepository $repo */
        $repo = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Replay::class)
        ;

        $oldest = $repo->findOldest();
        $newest = $repo->findNewest();
        $replays = $repo->findByTimeRange($after, $before);

        /** @var DefaultArray<string, array<Replay>> $replaysByDay */
        $replaysByDay = new DefaultArray([]);
        $summaries = QuickReplaySummary::summarizeReplays(
            $summaryService,
            $replays,
            static function (Replay $replay) use (&$replaysByDay) {
                $replaysByDay[$replay->getStartTime()->format('Y-m-d')][] = $replay;
            },
            static function (Replay $replay, \Exception $e) use ($logger) {
                $logger->warning('Replay ID {id} could not be summarized correctly', [
                    'id' => $replay->getId(),
                ]);
            }
        );

        return $this->render('replay/index.html.twig', [
            'replays' => $replaysByDay->getAsArray(),
            'summaries' => $summaries,
            'oldest_replay' => $oldest,
            'newest_replay' => $newest,
            'pagination' => [
                'after' => $after,
                'before' => $before,
            ],
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
     */
    public function showAction(int $id, string $filename, string $_format, ReplaySummaryService $summaryService, LoggerInterface $logger): Response
    {
        /** @var ReplayRepository $replayRepo */
        $replayRepo = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(Replay::class)
        ;
        $replay = $replayRepo->findOneBy([
            'id' => $id,
            'fileName' => $filename,
        ]);

        if ($replay === null) {
            throw $this->createNotFoundException();
        }

        $summaryService->summarizeFull($replay);
        $thumbnail = $replay->getMapThumbnail();

        $replayMap = null;

        if (($t = $replay->getMapThumbnail()) !== null) {
            $replayMap = $t->getKnownMap();
        }

        try {
            $replaySummary = [
                'id' => $replay->getId(),
                'filename' => $replay->getFileName(),
                'map' => $replayMap,
                'thumbnail' => $thumbnail,
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

    /**
     * Safely get an upper or lower bound \DateTime from a query parameter.
     *
     * @param Request $request The incoming HTTP request
     * @param string  $param   The name of the query parameter
     *
     * @return \DateTime|null Null is returned if there is no valid timestamp
     */
    private function safeGetTimestamp(Request $request, string $param): ?\DateTime
    {
        $timestamp = $request->get($param);

        if ($timestamp !== null) {
            try {
                $timestamp = new \DateTime($timestamp);
            } catch (\Exception $e) {
                $timestamp = null;
            }
        }

        return $timestamp;
    }
}
