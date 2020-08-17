<?php declare(strict_types=1);

/*
 * (c) Vladimir "allejo" Jimenez <me@allejo.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.md file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\KnownMap;
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
}
