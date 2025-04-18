<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MonPremierController
{
    #[Route('/index', name: 'index')]
    public function maPremiereReponse(): Response
    {
        return new Response('<html><title>Index</title><body>Bienvenue sur mon site</body></html>', Response::HTTP_OK);
    }
}
