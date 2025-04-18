<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, TranslatorInterface $translator, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $data = ['email' => '', 'password' => ''];

        $form = $this->createFormBuilder($data, ['csrf_protection' => false])
            ->add('email', EmailType::class, ['label' => $translator->trans('E-mail')])
            ->add('password', PasswordType::class, ['label' => $translator->trans('Mot de passe')])
            ->add('submit', SubmitType::class, ['label' => $translator->trans('Se connecter')])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Rechercher l'utilisateur par son email
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);

            if ($user && $passwordHasher->isPasswordValid($user, $data['password'])) {
                // Authentification réussie, redirige vers la page de succès
                $session = $request->getSession();
                $session->set('connect', true);
                return $this->redirectToRoute('app_register_success', ['login' => $data['email']]);
            } else {
                // Ajouter une erreur si l'authentification échoue
                $form->get('password')->addError(new FormError($translator->trans('Identifiants invalides')));
            }
        }

        return $this->render('login/login.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Request $request): Response
    {
        // Effacer la session
        $session = $request->getSession();
        $session->set('connect', false);

        return $this->redirectToRoute('app_register'); // Redirige vers la page d'accueil après déconnexion
    }
}
