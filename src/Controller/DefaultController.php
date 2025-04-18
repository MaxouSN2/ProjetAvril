<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function redirectToAccueil(): RedirectResponse
    {
        return $this->redirectToRoute('route_accueil');  // 'route_accueil' est le nom de la route pour /accueil
    }
}