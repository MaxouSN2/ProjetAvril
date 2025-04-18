<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CoursSQLController extends AbstractController
{
    #[Route('/cours-sql', name: 'sql')]
    public function about()
    {
        return $this->render('cours/sql.html.twig');
    }
}
