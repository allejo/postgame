<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\CaptureEvent;
use App\Entity\KillEvent;
use App\Entity\Player;
use App\Entity\Replay;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $match_count = $em->getRepository(Replay::class)->getSummaryCount();
        $player_activity = $em->getRepository(Player::class)->findMostActive();
        $player_captures = $em->getRepository(CaptureEvent::class)->findTopCappers();
        $top_killers = $em->getRepository(KillEvent::class)->findTopKillers();
        $top_victims = $em->getRepository(KillEvent::class)->findTopVictims();

        return $this->render('index.html.twig', [
            'match_count' => $match_count,
            'top_players' => $player_activity,
            'top_cappers' => $player_captures,
            'top_killers' => $top_killers,
            'top_victims' => $top_victims,
        ]);
    }
}
