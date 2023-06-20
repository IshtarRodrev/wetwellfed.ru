<?php

namespace App\Controller;

use App\Entity\Eater;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Form\RegistrationType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class EaterController extends AbstractController
{
    /**
     * @Route("/registrate", name="app_register")
     * @return Response
     */
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // ... e.g. get the user data from a registration form
        $user = new Eater();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            // encode the plain password
            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword(),
            );
            $user->setPassword($hashedPassword);
            $user->setRoles(["ROLE_EATER"]);

            //NOTE: формула Харриса-Бенедикта https://en.wikipedia.org/wiki/Harris–Benedict_equation
            //      BMR для мужчин = 88,36 + (13,4 × вес в кг) + (4,8 × рост в см) – (5,7 × возраст в годах).
            //      BMR для женщин = 447,6 + (9,2 × вес в кг) + (3,1 × рост в см) – (4,3 × возраст в годах).
            //NOTE: Формула по Миффлину – Сан Жеору:
            //      Женская формула: 10 х вес + 6,25 х рост – 5 х возраст – 161;
            //      Мужская формула: 10 х вес + 6,25 х рост – 5 х года + 5.
            // Формула по Харрису-Бенедикту:
            //      Женская формула: 655,1 + 9,563 х вес + 1,85 х рост — 4,676 х возраст;
            //      Мужская формула: 66,5 + 13,75 х вес + 5,003 х рост – 6,775 х возраст

            $now = new \DateTime(); // текущее время на сервере
            $interval = $now->diff($user->getBirthdate());

            $BMR = 1000;
            if ($user->getSex() == 1) { // BMR для женщин
                $BMR = 447.6 + (9.2 * $user->getWeight()) + (3.1 * $user->getHeight()) - (4.3 * $interval->y);
            } elseif ($user->getSex() == 0) { // BMR для мужчин
                $BMR = 88.36 + (13.4 * $user->getWeight()) + (4.8 * $user->getHeight()) - (5.7 * $interval->y);
            }
            $user->setKcalDayNorm($BMR);
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('meal');
        }

        return $this->render('eater/registrate.html.twig', [
            'registrationType' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="app_login")]
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('eater/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error,
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")]
     * @return Response
     */
    public function logout(AuthenticationUtils $authenticationUtils): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
        return $this->redirectToRoute('meal');
    }
}