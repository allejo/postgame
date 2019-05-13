<?php

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
