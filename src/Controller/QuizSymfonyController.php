<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizSymfonyController extends AbstractController
{
    #[Route('/quiz-symfony', name: 'quiz-symfony')]
    public function index(Request $request, SessionInterface $session)
    {
        $questions = $this->getQuestions();
        $totalQuestions = count($questions);
        $currentQuestion = (int) $request->query->get('q', 0);
        $userAnswers = $session->get('quiz_symfony_answers', []);

        if ($request->isMethod('POST')) {
            $answer = $request->request->get('answer');

            if ($answer !== null) {
                $userAnswers[$currentQuestion] = (int) $answer;
                $session->set('quiz_symfony_answers', $userAnswers);
            }

            if ($currentQuestion + 1 < $totalQuestions) {
                return $this->redirectToRoute('quiz-symfony', ['q' => $currentQuestion + 1]);
            } else {
                $goodAnswers = 0;
                $wrongAnswers = 0;
                $correctAnswers = [];

                foreach ($questions as $index => $question) {
                    $userAnswer = $userAnswers[$index] ?? -1;
                    $correctAnswer = $question['answer'];

                    if ($userAnswer === $correctAnswer) {
                        $goodAnswers++;
                        $correctAnswers[] = $question['options'][$correctAnswer];
                    } elseif ($userAnswer !== -1) {
                        $wrongAnswers++;
                    }
                }

                $score = [
                    'correct' => $goodAnswers,
                    'incorrect' => $wrongAnswers,
                    'total' => $totalQuestions
                ];

                return $this->render('quiz/quiz-symfony.html.twig', [
                    'questions' => $questions,
                    'score' => $score,
                    'correctAnswers' => $correctAnswers,
                    'userAnswers' => $userAnswers,
                    'showResults' => true
                ]);
            }
        }

        return $this->render('quiz/quiz-symfony.html.twig', [
            'questions' => [$questions[$currentQuestion]],
            'currentQuestion' => $currentQuestion,
            'totalQuestions' => $totalQuestions,
            'userAnswers' => $userAnswers,
            'showResults' => false
        ]);
    }

    #[Route('/quiz-symfony/export/csv', name: 'quiz_symfony_export_csv')]
    public function exportCsv(SessionInterface $session): StreamedResponse
    {
        $questions = $this->getQuestions();
        $userAnswers = $session->get('quiz_symfony_answers', []);

        $goodAnswers = 0;
        $wrongAnswers = 0;
        $totalQuestions = count($questions);

        $response = new StreamedResponse(function () use ($questions, $userAnswers, $goodAnswers, $wrongAnswers, $totalQuestions) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, ['N°', 'Question', 'Votre réponse', 'Bonne réponse', 'Résultat']);

            foreach ($questions as $index => $question) {
                $userAnswer = $userAnswers[$index] ?? null;
                $correctAnswer = $question['answer'];
                $result = ($userAnswer === $correctAnswer) ? '✔️ Correct' : '❌ Incorrect';

                if ($userAnswer === $correctAnswer) {
                    $goodAnswers++;
                } elseif ($userAnswer !== null) {
                    $wrongAnswers++;
                }

                fputcsv($handle, [
                    $index + 1,
                    $question['question'],
                    $userAnswer !== null ? $question['options'][$userAnswer] : 'Aucune réponse',
                    $question['options'][$correctAnswer],
                    $result
                ]);
            }

            // Ajouter un résumé du score à la fin
            fputcsv($handle, []);
            fputcsv($handle, ['Score total']);
            fputcsv($handle, ['Bonnes réponses', $goodAnswers]);
            fputcsv($handle, ['Mauvaises réponses', $wrongAnswers]);
            fputcsv($handle, ['Score final', "{$goodAnswers}/{$totalQuestions}"]);

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="quiz-symfony-resultats.csv"');

        return $response;
    }

    private function getQuestions(): array
    {
        return [
            [
                'question' => 'Quel est le rôle principal du contrôleur dans l’architecture MVC de Symfony ?',
                'options' => [
                    'Gérer l’affichage HTML',
                    'Gérer la base de données',
                    'Traiter les requêtes et retourner des réponses',
                    'Créer des entités'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quelle commande permet de créer un nouveau projet Symfony minimal ?',
                'options' => [
                    'symfony new mon_projet',
                    'composer create-project symfony/skeleton mon_projet',
                    'php new-project Symfony mon_projet',
                    'symfony install mon_projet'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quelle URL est généralement utilisée pour accéder à une application en développement avec symfony serve ?',
                'options' => [
                    'http://127.0.0.1:9000',
                    'http://localhost:8080',
                    'http://localhost:8000',
                    'http://symfony.local'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quel est le moteur de template utilisé par Symfony pour afficher des vues ?',
                'options' => [
                    'Blade',
                    'Smarty',
                    'Twig',
                    'Volt'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quel composant Symfony est utilisé pour gérer les opérations sur les bases de données ?',
                'options' => [
                    'Doctrine ORM',
                    'Database Manager',
                    'PDO Core',
                    'Eloquent'
                ],
                'answer' => 0
            ],
            [
                'question' => 'Dans un contrôleur Symfony, quelle méthode est utilisée pour rendre un template Twig ?',
                'options' => [
                    'view()',
                    'render()',
                    'show()',
                    'display()'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quelle annotation permet de définir une route dans un contrôleur Symfony ?',
                'options' => [
                    '@Get',
                    '@Routing',
                    '@Route',
                    '@Path'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quel est le rôle du composant Form dans Symfony ?',
                'options' => [
                    'Créer des sessions utilisateurs',
                    'Créer des formulaires HTML et gérer la validation',
                    'Générer des migrations',
                    'Mettre à jour la base de données'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quel fichier est utilisé pour configurer les routes dans Symfony (hors annotations) ?',
                'options' => [
                    'routes.yaml',
                    'routes.php',
                    'routing.yml',
                    'router.config'
                ],
                'answer' => 0
            ],
            [
                'question' => 'Pourquoi Symfony est-il adapté aux projets professionnels ?',
                'options' => [
                    'Il est gratuit',
                    'Il propose des fonctionnalités avancées et une bonne testabilité',
                    'Il est uniquement conçu pour les petits projets',
                    'Il ne nécessite aucune configuration'
                ],
                'answer' => 1
            ]
        ];
    }

}
