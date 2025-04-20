<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizRecapController extends AbstractController
{
    #[Route('/quiz-recap', name: 'quiz-recap')]
    public function index(Request $request, SessionInterface $session)
    {
        $questions = $this->getQuestions();
        $totalQuestions = count($questions);
        $currentQuestion = (int) $request->query->get('q', 0);
        $userAnswers = $session->get('quiz_recap_answers', []);

        if ($request->isMethod('POST')) {
            $answer = $request->request->get('answer');

            if ($answer !== null) {
                $userAnswers[$currentQuestion] = (int) $answer;
                $session->set('quiz_recap_answers', $userAnswers);
            }

            if ($currentQuestion + 1 < $totalQuestions) {
                return $this->redirectToRoute('quiz-recap', ['q' => $currentQuestion + 1]);
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

                return $this->render('recap/quiz-recap.html.twig', [
                    'questions' => $questions,
                    'score' => $score,
                    'correctAnswers' => $correctAnswers,
                    'userAnswers' => $userAnswers,
                    'showResults' => true
                ]);
            }
        }

        return $this->render('recap/quiz-recap.html.twig', [
            'questions' => [$questions[$currentQuestion]],
            'currentQuestion' => $currentQuestion,
            'totalQuestions' => $totalQuestions,
            'userAnswers' => $userAnswers,
            'showResults' => false
        ]);
    }

    #[Route('/quiz-recap/export/csv', name: 'quiz_recap_export_csv')]
    public function exportCsv(SessionInterface $session): StreamedResponse
    {
        $questions = $this->getQuestions();
        $userAnswers = $session->get('quiz_recap_answers', []);

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
        $response->headers->set('Content-Disposition', 'attachment; filename="quiz-recap-resultats.csv"');

        return $response;
    }

    private function getQuestions(): array
    {
        return [
            [
                'question' => 'Que signifie PHP ?',
                'options' => [
                    'Private Home Page',
                    'Personal Hypertext Processor',
                    'PHP: Hypertext Preprocessor',
                    'Page Handling Protocol'
                ],
                'answer' => 2
            ],
            [
                'question' => 'PHP est un langage...',
                'options' => [
                    'Côté client',
                    'Côté serveur',
                    'Statique uniquement',
                    'Compilé uniquement'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quelle syntaxe est correcte pour déclarer une variable en PHP ?',
                'options' => [
                    'let nom = "Jean";',
                    '$nom = "Jean";',
                    'var nom = "Jean";',
                    'string nom = "Jean";'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Que va afficher ce code : `$age = 20; if ($age >= 18) echo "Majeur"; else echo "Mineur";`',
                'options' => [
                    'Mineur',
                    'Erreur de syntaxe',
                    'Rien',
                    'Majeur'
                ],
                'answer' => 3
            ],
            [
                'question' => 'Quelle boucle affichera les nombres de 1 à 5 en PHP ?',
                'options' => [
                    'for ($i = 1; $i <= 5; $i++)',
                    'loop i from 1 to 5',
                    'foreach (1..5 as $i)',
                    'while ($i < 5)'
                ],
                'answer' => 0
            ],
            [
                'question' => 'Quelle est la bonne façon de créer une fonction en PHP ?',
                'options' => [
                    'function saluer($prenom) { return "Salut " . $prenom; }',
                    'def saluer($prenom): return "Salut " + $prenom',
                    'fun saluer(prenom):',
                    'function:saluer($prenom) =>'
                ],
                'answer' => 0
            ],
            [
                'question' => 'Quel mot-clé SQL est utilisé pour récupérer des données ?',
                'options' => ['GET', 'SELECT', 'FETCH', 'SHOW'],
                'answer' => 1
            ],
            [
                'question' => 'Que permet la clause WHERE dans une requête SQL ?',
                'options' => [
                    'Ajouter une ligne',
                    'Trier les résultats',
                    'Filtrer les résultats',
                    'Afficher toutes les lignes'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quelle requête ajoute un nouvel enregistrement ?',
                'options' => [
                    'ADD INTO utilisateurs ...',
                    'UPDATE utilisateurs SET ...',
                    'INSERT INTO utilisateurs (...) VALUES (...)',
                    'CREATE utilisateurs (...)'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quelle commande permet de modifier une donnée existante ?',
                'options' => ['SET', 'UPDATE', 'INSERT', 'MODIFY'],
                'answer' => 1
            ],
            [
                'question' => 'Quelle commande supprime un enregistrement ?',
                'options' => ['REMOVE', 'DELETE', 'DROP', 'CLEAR'],
                'answer' => 1
            ],
            [
                'question' => 'Que fait cette requête : `SELECT * FROM utilisateurs ORDER BY age DESC` ?',
                'options' => [
                    'Elle supprime les utilisateurs par âge',
                    'Elle trie les utilisateurs par âge décroissant',
                    'Elle trie les utilisateurs par nom',
                    'Elle insère de nouveaux utilisateurs'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quel mot-clé permet de combiner des données de plusieurs tables ?',
                'options' => ['UNION', 'GROUP', 'JOIN', 'LINK'],
                'answer' => 2
            ],
            [
                'question' => 'Symfony est...',
                'options' => [
                    'Un CMS',
                    'Un langage de programmation',
                    'Un framework PHP',
                    'Un éditeur de texte'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quel est le rôle du contrôleur dans Symfony ?',
                'options' => [
                    'Gérer la mise en page',
                    'Afficher les données',
                    'Traiter les requêtes et générer des réponses',
                    'Créer des fichiers CSS'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quel moteur de template est utilisé avec Symfony ?',
                'options' => ['Blade', 'Smarty', 'Twig', 'Mustache'],
                'answer' => 2
            ],
            [
                'question' => 'Quelle commande permet de créer un projet Symfony ?',
                'options' => [
                    'symfony new projet',
                    'composer create-project symfony/skeleton mon_projet',
                    'php create symfony-project',
                    'symfony start-project'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quel composant Symfony est utilisé pour envoyer des e-mails ?',
                'options' => ['Form', 'Mailer', 'Security', 'Notifier'],
                'answer' => 1
            ],
            [
                'question' => 'Quel est le format par défaut des fichiers de vue Twig ?',
                'options' => ['.twig.html', '.php', '.tpl', '.html.twig'],
                'answer' => 3
            ],
            [
                'question' => 'Symfony repose sur quelle architecture ?',
                'options' => ['LAMP', 'MVC', 'MVVM', 'SPA'],
                'answer' => 1
            ],
            [
                'question' => 'Quel composant est utilisé pour interagir avec les bases de données dans Symfony ?',
                'options' => ['Doctrine ORM', 'Eloquent', 'PDO Only', 'EntityManagerLite'],
                'answer' => 0
            ],
        ];
    }
    
}
