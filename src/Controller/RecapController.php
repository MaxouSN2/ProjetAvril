<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class RecapController extends AbstractController
{
    #[Route('/recap', name: 'recap')]
    public function about()
    {
        return $this->render('recap/recap.html.twig');
    }
}
