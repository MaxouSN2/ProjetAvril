<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizSQLController extends AbstractController
{
    #[Route('/quiz-sql', name: 'quiz-sql')]
    public function index(Request $request, SessionInterface $session)
    {
        $questions = $this->getQuestions();
        $totalQuestions = count($questions);
        $currentQuestion = (int) $request->query->get('q', 0);
        $userAnswers = $session->get('quiz_sql_answers', []);

        if ($request->isMethod('POST')) {
            $answer = $request->request->get('answer');

            if ($answer !== null) {
                $userAnswers[$currentQuestion] = (int) $answer;
                $session->set('quiz_sql_answers', $userAnswers);
            }

            if ($currentQuestion + 1 < $totalQuestions) {
                return $this->redirectToRoute('quiz-sql', ['q' => $currentQuestion + 1]);
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

                return $this->render('quiz/quiz-sql.html.twig', [
                    'questions' => $questions,
                    'score' => $score,
                    'correctAnswers' => $correctAnswers,
                    'userAnswers' => $userAnswers,
                    'showResults' => true
                ]);
            }
        }

        return $this->render('quiz/quiz-sql.html.twig', [
            'questions' => [$questions[$currentQuestion]],
            'currentQuestion' => $currentQuestion,
            'totalQuestions' => $totalQuestions,
            'userAnswers' => $userAnswers,
            'showResults' => false
        ]);
    }

    #[Route('/quiz-sql/export/csv', name: 'quiz_sql_export_csv')]
    public function exportCsv(SessionInterface $session): StreamedResponse
    {
        $questions = $this->getQuestions();
        $userAnswers = $session->get('quiz_sql_answers', []);

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
        $response->headers->set('Content-Disposition', 'attachment; filename="quiz-sql-resultats.csv"');

        return $response;
    }

    private function getQuestions(): array
    {
        return [
            [
                'question' => 'Que signifie SQL ?',
                'options' => ['Standard Query Level', 'Structured Query Language', 'Server Query Logic', 'Simple Query Language'],
                'answer' => 1
            ],
            [
                'question' => 'Quelle commande SQL est utilisée pour récupérer des données ?',
                'options' => ['GET', 'SELECT', 'RETRIEVE', 'FETCH'],
                'answer' => 1
            ],
            [
                'question' => 'Laquelle de ces requêtes permet d’insérer des données ?',
                'options' => ['INSERT INTO ... VALUES (...)', 'ADD VALUES TO ...', 'APPEND TO ...', 'SET INTO ...'],
                'answer' => 0
            ],
            [
                'question' => 'Comment filtre-t-on les résultats dans une requête SQL ?',
                'options' => ['USING', 'FILTER BY', 'WHERE', 'IF'],
                'answer' => 2
            ],
            [
                'question' => 'Quelle commande est utilisée pour supprimer des enregistrements ?',
                'options' => ['REMOVE', 'DELETE', 'DROP', 'CLEAR'],
                'answer' => 1
            ],
            [
                'question' => 'Comment trier les résultats dans une requête SQL ?',
                'options' => ['SORT BY', 'GROUP BY', 'ORDER BY', 'FILTER ORDER'],
                'answer' => 2
            ],
            [
                'question' => 'Quelle clause permet de mettre à jour des données existantes ?',
                'options' => ['SET', 'MODIFY', 'CHANGE', 'UPDATE'],
                'answer' => 3
            ],
            [
                'question' => 'Que signifie CRUD en base de données ?',
                'options' => ['Create, Read, Update, Delete', 'Copy, Run, Upload, Download', 'Code, Review, Update, Debug', 'Create, Remove, Undo, Download'],
                'answer' => 0
            ],
            [
                'question' => 'Quelle commande permet de joindre deux tables ?',
                'options' => ['MERGE', 'UNION', 'JOIN', 'APPEND'],
                'answer' => 2
            ],
            [
                'question' => 'Que fait cette requête : SELECT * FROM utilisateurs WHERE age > 30;',
                'options' => [
                    'Elle sélectionne tous les utilisateurs ayant plus de 30 ans.',
                    'Elle insère un utilisateur ayant plus de 30 ans.',
                    'Elle met à jour l’âge de tous les utilisateurs à 30.',
                    'Elle supprime tous les utilisateurs de plus de 30 ans.'
                ],
                'answer' => 0
            ]
        ];
    }
}
