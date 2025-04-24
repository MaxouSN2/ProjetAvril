<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'stats')]
    public function stats(SessionInterface $session)
    {
        $scores = $session->get('quiz_recap_all_scores', []);

        return $this->render('stats/stats.html.twig', [
            'scores' => $scores
        ]);
    }

    #[Route('/stats/reset', name: 'stats_reset')]
    public function reset(SessionInterface $session)
    {
        // Réinitialiser les scores et les réponses en session
        $session->remove('quiz_recap_all_scores');
        $session->remove('quiz_recap_answers');

        // Optionnel : Ajout d'un message flash de succès
        $this->addFlash('success', 'Les statistiques ont été réinitialisées.');

        return $this->redirectToRoute('stats');
    }
}
