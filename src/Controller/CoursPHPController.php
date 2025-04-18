<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CoursPHPController extends AbstractController
{
    #[Route('/cours-php', name: 'php')]
    public function about()
    {
        return $this->render('cours/php.html.twig');
    }
}
