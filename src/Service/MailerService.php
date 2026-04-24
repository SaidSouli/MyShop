<?php

namespace App\Service;

use App\Entity\Order;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailerService
{
    public function __construct(private MailerInterface $mailer, private Environment $twig)
    {
    }

    public function sendOrderConfirmation(Order $order): void
    {
        $html = $this->twig->render('emails/order_confirmation.html.twig', [
            'order' => $order,
        ]);
        $email = (new Email())
            ->from('noreply@myshop.com')
            ->to($order->getCustomer()->getEmail())
            ->subject(sprintf('Order Confirmation - #%d', $order->getReference()))
            ->html($html);

        $this->mailer->send($email);
    }




}