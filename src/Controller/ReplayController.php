<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ReplayController extends AbstractController
{
    /**
     * @Route("/replay", name="replay")
     */
    public function index()
    {
        return $this->render('replay/index.html.twig', [
            'controller_name' => 'ReplayController',
        ]);
    }
}
