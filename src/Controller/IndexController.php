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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        $player_activity = $em->getRepository(Player::class)->findMostActive();
        $player_captures = $em->getRepository(CaptureEvent::class)->findTopCappers();
        $top_killers = $em->getRepository(KillEvent::class)->findTopKillers();
        $top_victims = $em->getRepository(KillEvent::class)->findTopVictims();

        return $this->render('index.html.twig', [
            'top_players' => $player_activity,
            'top_cappers' => $player_captures,
            'top_killers' => $top_killers,
            'top_victims' => $top_victims,
        ]);
    }
}
