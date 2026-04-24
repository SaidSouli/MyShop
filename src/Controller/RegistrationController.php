<?php

namespace App\Controller;

use App\Entity\User;
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

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager,Security $security , UserAuthenticatorInterface $userAuthenticator , AppAuthenticator $appAuthenticator): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_shop_index');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          
        $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
        $user->setRoles([]);

            $user->setIsVerified(false);


            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (
                    \Doctrine\DBAL\Exception\UniqueConstraintViolationException $e
                ) {
        $this->addFlash(
        'error',
        'An account with this email already exists.'
        );
        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
}

           $userAuthenticator->authenticateUser(
                $user,
                $appAuthenticator,  
                $request
            );
        
        $this->addFlash('success', sprintf('Welcome %s! Your account has been created successfully.', $user->getFirstName())    );
            return $this->redirectToRoute('app_shop_index');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
        ]);
    }
}
