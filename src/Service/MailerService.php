<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class MailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private EntityManagerInterface $entityManager
    ) {}

    public function sendOrderConfirmation(Order $order): void
    {
        // Switch to TemplatedEmail for consistency and cleaner code
        $email = (new TemplatedEmail())
            ->from(new Address('noreply@myshop.com', 'MyShop'))
            ->to($order->getCustomer()->getEmail())
            ->subject(sprintf('Order Confirmation - #%s', $order->getReference())) // Fixed: Reference might be a string
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context([
                'order' => $order,
            ]);

        $this->mailer->send($email);
    }

    public function sendConfirmationEmail(string $verifyRoute, User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyRoute,
            (string) $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()] 
        );

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@myshop.com', 'MyShop'))
            ->to($user->getEmail())
            ->subject('Please Confirm your Email')
            ->htmlTemplate('emails/verifyUser.html.twig')
            ->context([
                'user' => $user,
                'signedUrl' => $signatureComponents->getSignedUrl(),
            ]);

        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {
       
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),
            $user->getEmail()
        );

        $user->setIsVerified(true);
        $this->entityManager->flush();
    }
}