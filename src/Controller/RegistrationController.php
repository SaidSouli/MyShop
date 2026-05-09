<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailerService;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\AppAuthenticator;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private MailerService $mailerService // Tip: Name this mailerService for clarity
    ) {}

    #[Route('/register', name: 'app_register')]
    public function register(UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $appAuthenticator, Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Good: Redirect if already logged in
        if ($this->getUser()) {
            return $this->redirectToRoute('app_shop_index');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            
            // ROLE_USER is usually handled by the Entity's default value
            $user->setIsVerified(false);

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                // 2. Send the email
                $this->mailerService->sendConfirmationEmail('app_verify_email', $user);

                // 3. Auto-authenticate the user
                $userAuthenticator->authenticateUser($user, $appAuthenticator, $request);

                $this->addFlash('success', 'Welcome! Please check your email to verify your account.');
                return $this->redirectToRoute('app_shop_index');

            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'An account with this email already exists.');
                // Note: Don't return here, let it fall through to render the form with the error
            }
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $em): Response
    {
        // Get ID from the query string (app_verify_email?id=...)
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $em->getRepository(User::class)->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        try {
            $this->mailerService->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $e) {
            
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', 'Your email has been verified!');
        return $this->redirectToRoute('app_shop_index'); 
    }
}

