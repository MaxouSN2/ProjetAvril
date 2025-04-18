<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClientController extends AbstractController
{
    #[Route('/client/prenom/{prenom}', name: 'client_prenom', requirements: ['prenom' => '^[a-zA-Z]+(-[a-zA-Z]+)*$'])]
    public function info(string $prenom): Response
    {
        // Liste des clients pour l'exemple
        $clients = [
            'rene' => ['Rene Dupres', 'Rene Dupond'],
            'jacqueline' => ['Jacqueline Dubois'],
            'marie' => ['Marie Lefevre', 'Marie-Claude Lefevre'],
            'zouhir' => ['Zouhir KRIM'],
            'maxime' => ['LEBRUN Maxime'],
            'mathieu' => ['Mathieu BROGLY']
        ];

        // Vérifie si le prénom existe dans la liste des clients
        $result = $clients[strtolower($prenom)] ?? [];

        // Si aucun client trouvé, renvoyer un message alternatif
        if (empty($result)) {
            return new Response(
                'Aucune personne dans la base de donnée avec le prénom "' . htmlspecialchars($prenom) . '".'
            );
        }

        // Retourne la réponse avec les clients trouvés
        return new Response(
            'Clients avec le prénom "' . htmlspecialchars($prenom) . '" : ' . implode(', ', $result)
        );
    }

  // 2 méthodes dans mon fichier ClientController.php

public function index(Request $request): Response
{
    $this->render('home/index.html.twig');

    return new Response('La route est accessible.');
}

public function ferme(): Response
{
    return new Response('Le site est fermé.', Response::HTTP_FORBIDDEN);
}
}
