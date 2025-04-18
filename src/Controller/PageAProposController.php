<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PageAProposController extends AbstractController
{
    #[Route('/a-propos', name: 'about')]
    public function about()
    {
        return $this->render('apropos/about.html.twig');
    }
}
