<?php

namespace App\Controller;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Stripe\Checkout\Session;
use Stripe\Stripe;
#[Route('/payment', name: 'app_payment')]
final class PaymentController extends AbstractController
{
    
    public function __construct( private EntityManagerInterface $entityManager , private string $stripeSecretKey ) {
    }

    // ─────────────────────────────────────────
    // GET /payment/success
    // stripe redirect after successful payment
    // ─────────────────────────────────────────
    #[Route('/success', name: '_success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');
        if (!$sessionId) {
            $this->addFlash('error', 'Invalid payment session.');
            return $this->redirectToRoute('app_shop_index');
        }

        // retrive the Stripe session to get the order reference
        Stripe::setApiKey($this->stripeSecretKey);
        try {
            $stripeSession = Session::retrieve($sessionId);
            $reference = $stripeSession->metadata->order_reference ?? null;
            } catch (\Exception $e) {
            $this->addFlash('error', 'Error retrieving payment session.');
            return $this->redirectToRoute('app_shop_index');
        }
        if (!$reference) {
            $this->addFlash('error', 'Order reference not found in payment session.');
            return $this->redirectToRoute('app_shop_index');
        }
        // find the order
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        if (!$order || $order->getCustomer() !== $this->getUser()) {
            $this->addFlash('error', 'Order not found or access denied.');
            return $this->redirectToRoute('app_shop_index');
        }

        // Here you would typically verify the session with Stripe's API
        // and update the corresponding order status in your database.

        $this->addFlash('success', 'Payment successful! Your order is being processed.');
        return $this->redirectToRoute('app_checkout_confirmation', ['reference' => $order->getReference()]);
    }

    // ─────────────────────────────────────────
    // GET /payment/cancel
    // stripe redirect here if user clicks "back" during payment
    // ─────────────────────────────────────────
    #[Route('/cancel', name: '_cancel', methods: ['GET'])]
    public function cancel(Request $request): Response
    {
        $reference = $request->query->get('reference');
        return $this->render('payment/cancel.html.twig', [
            'reference' => $reference
        ]);
    }
    // ─────────────────────────────────────────
    // GET /payment/retry/{reference}
    // shown when Stripe session creation fails, allows user to retry payment
    // ─────────────────────────────────────────
    #[Route('/retry/{reference}', name: '_retry', methods: ['GET'])]
    public function retry(string $reference): Response
    {        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        if (!$order || $order->getCustomer() !== $this->getUser()) {
            $this->addFlash('error', 'Order not found or access denied.');
        }
        return $this->render('payment/retry.html.twig', [
            'order' => $order
        ]);
    }
}
