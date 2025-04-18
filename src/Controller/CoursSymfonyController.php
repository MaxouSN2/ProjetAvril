<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CoursSymfonyController extends AbstractController
{
    #[Route('/cours-symfony', name: 'symfony')]
    public function about()
    {
        return $this->render('cours/symfony.html.twig');
    }
}
