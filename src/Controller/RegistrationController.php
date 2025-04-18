<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, TranslatorInterface $translator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $data = [
            'email' => '',
            'password' => '',
            'confirmPassword' => '',
        ];

        $form = $this->createFormBuilder($data, [
                'csrf_protection' => false, // Désactivation de la vérification CSRF
            ])
            ->add('email', EmailType::class, [
                'label' => $translator->trans('E-mail'),
                'constraints' => [
                    new NotBlank(['message' => $translator->trans('Veuillez saisir un e-mail.')]),
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => $translator->trans('Mot de passe'),
                'constraints' => [
                    new NotBlank(['message' => $translator->trans('Veuillez saisir un mot de passe.')]),
                    new Regex([
                        'pattern' => '/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}/',
                        'message' => $translator->trans('Le mot de passe doit contenir des majuscules, des minuscules, des chiffres et des caractères spéciaux'),
                    ]),
                ]
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => $translator->trans('Confirmer le mot de passe'),
                'constraints' => [
                    new NotBlank(['message' => $translator->trans('Veuillez confirmer votre mot de passe.')]),
                ]
            ])
            ->add('submit', SubmitType::class, ['label' => $translator->trans('S\'inscrire')])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData(); // Récupérer les données soumises après validation

            if ($data['password'] !== $data['confirmPassword']) {
                $form->get('confirmPassword')->addError(new FormError($translator->trans('Les mots de passe ne correspondent pas')));
            }

            if ($form->isValid() && $data['password'] === $data['confirmPassword']) {
                // Création de l'utilisateur
                $user = new User();
                $user->setEmail($data['email']);

                // Hachage du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
                $user->setPassword($hashedPassword);

                // Sauvegarde de l'utilisateur dans la base de données
                $entityManager->persist($user);
                $entityManager->flush();

                // Redirection vers la page de succès
                return $this->redirectToRoute('app_register_success', ['login' => $data['email']]);
            }
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register/success/{login}', name: 'app_register_success')]
    public function success(string $login): Response
    {
        return $this->render('registration/success.html.twig', [
            'login' => $login,
        ]);
    }
}
