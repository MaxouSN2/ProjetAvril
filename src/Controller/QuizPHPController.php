<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class QuizPHPController extends AbstractController
{
    #[Route('/quiz-php', name: 'quiz-php')]
    public function index(Request $request, SessionInterface $session)
    {
        $questions = $this->getQuestions();
        $totalQuestions = count($questions);

        // Numéro de la question courante via ?q=0
        $currentQuestion = (int) $request->query->get('q', 0);

        // Récupération des réponses précédentes
        $userAnswers = $session->get('quiz_php_answers', []);

        // Traitement du formulaire (POST)
        if ($request->isMethod('POST')) {
            $answer = $request->request->get('answer');

            if ($answer !== null) {
                $userAnswers[$currentQuestion] = (int) $answer;
                $session->set('quiz_php_answers', $userAnswers);
            }

            // Passage à la question suivante
            if ($currentQuestion + 1 < $totalQuestions) {
                return $this->redirectToRoute('quiz-php', ['q' => $currentQuestion + 1]);
            } else {
                // Fin du quiz : calcul du score
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

                // Affichage des résultats
                return $this->render('quiz/quiz-php.html.twig', [
                    'questions' => $questions,
                    'score' => $score,
                    'correctAnswers' => $correctAnswers,
                    'userAnswers' => $userAnswers,
                    'showResults' => true
                ]);
            }
        }

        // Affichage de la question courante
        return $this->render('quiz/quiz-php.html.twig', [
            'questions' => [$questions[$currentQuestion]],
            'currentQuestion' => $currentQuestion,
            'totalQuestions' => $totalQuestions,
            'userAnswers' => $userAnswers,
            'showResults' => false
        ]);
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
                    'Programming Home Page'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quel symbole est utilisé pour déclarer une variable en PHP ?',
                'options' => [
                    '&',
                    '#',
                    '$',
                    '%'
                ],
                'answer' => 2
            ],
            [
                'question' => 'Quelle fonction est utilisée pour afficher du texte à l’écran en PHP ?',
                'options' => [
                    'print()',
                    'echo',
                    'display()',
                    'show()'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Comment écrire un commentaire sur une seule ligne en PHP ?',
                'options' => [
                    '/* commentaire */',
                    '// commentaire',
                    '<!-- commentaire -->',
                    '# commentaire'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quelle extension de fichier est utilisée pour les fichiers PHP ?',
                'options' => [
                    '.html',
                    '.php',
                    '.js',
                    '.css'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quelle superglobale contient les données envoyées par un formulaire en POST ?',
                'options' => [
                    '$_GET',
                    '$_POST',
                    '$_FORM',
                    '$_DATA'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quelle est la bonne façon de démarrer un bloc PHP ?',
                'options' => [
                    '<php>',
                    '<?php',
                    '<?',
                    '<!php>'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quel mot-clé est utilisé pour définir une fonction en PHP ?',
                'options' => [
                    'function',
                    'define',
                    'create',
                    'method'
                ],
                'answer' => 0
            ],
            [
                'question' => 'Quel opérateur est utilisé pour la concaténation en PHP ?',
                'options' => [
                    '+',
                    '.',
                    '&',
                    '++'
                ],
                'answer' => 1
            ],
            [
                'question' => 'Quelle fonction permet de compter le nombre d’éléments dans un tableau ?',
                'options' => [
                    'count()',
                    'size()',
                    'length()',
                    'elements()'
                ],
                'answer' => 0
            ]
        ];
    }
}
