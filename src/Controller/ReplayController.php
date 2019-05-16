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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReplayController extends AbstractController
{
    /**
     * @Route("/replays", name="replay")
     */
    public function index(): Response
    {
        return $this->render('replay/index.html.twig', [
            'controller_name' => 'ReplayController',
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
     *
     * @return Response
     */
    public function show(int $id, string $filename, string $_format, ReplaySummaryService $summaryService): Response
    {
        $em = $this->getDoctrine()->getManager();
        $replay = $em->getRepository(Replay::class)->findOneBy([
            'id' => $id,
            'fileName' => $filename,
        ]);

        if ($replay === null) {
            throw $this->createNotFoundException();
        }

        $summary = $summaryService->getSummary($replay);

        if ($_format === 'json') {
            return $this->json([
                'players' => $summary,
            ]);
        }

        return $this->render('replay/show.html.twig', [
            'replay' => $replay,
            'players' => $summary,
        ]);
    }
}
