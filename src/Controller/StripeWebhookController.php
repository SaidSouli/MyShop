<?php

namespace App\Controller;   

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;




class StripeWebhookController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private string $stripeSecretKey,
        private string $stripeWebhookSecret,

    ) {
        Stripe::setApiKey($this->stripeSecretKey);
    }
    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function handle(Request $request): JsonResponse
    {
        // Read the raw body and signature header - must happen before parsing
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('stripe-signature');

        // Verify the webhook signature
        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->stripeWebhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return new JsonResponse(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            return new JsonResponse(['error' => 'Invalid signature'], Response::HTTP_BAD_REQUEST);
        }

        // Route to correct handler based on event type
        switch ($event->type) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event);
                break;
            case 'checkout.session.expired':
                $this->handleCheckoutSessionExpired($event);
                break;
            case 'charge.refunded':
                $this->handleChargeRefunded($event);
                break;
            // Handle other event types as needed
            default:
                // Unexpected event type
                break;
        }
        return new JsonResponse(['status' => 'received']);
    }
            
    private function handleCheckoutSessionCompleted(Event $event): void
    {
        $session = $event->data->object;
        $reference = $session->metadata->order_reference ?? null;
        if (!$reference) {
            // Log missing reference
            return;
        }
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        if (!$order) {
            // Log order not found
            return;
        }
        if ($order->getStatus() !== OrderStatus::PENDING) {
            // Already processed
            return;
        }
        $order->setStatus(OrderStatus::PAID);
        

        // store stripe's payment intent id for future reference (refunds, etc)
        if ($session->payment_intent) {
            $order->setStripePaymentIntentId($session->payment_intent);
        }
        $this->entityManager->flush();

        // Send confirmation email
        try {
            $this->mailerService->sendOrderConfirmation($order);
        } catch (\Exception $e) {
            // Log email failure but don't fail the order
        }
    }
    private function handleCheckoutSessionExpired(Event $event): void
    {
        $session = $event->data->object;
        $reference = $session->metadata->order_reference ?? null;
        if (!$reference) {
            // Log missing reference
            return;
        }
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['reference' => $reference]);
        if (!$order) {
            // Log order not found
            return;
        }
        if ($order->getStatus() !== OrderStatus::PENDING) {
            // Already processed
            return;
        }
        $order->setStatus(OrderStatus::CANCELLED);
        // restore stock for each item in the order
        foreach ($order->getOrderItems() as $item) {
            if ($item->getProduct()) {
                $product = $item->getProduct();
                $product->setStock($product->getStock() + $item->getQuantity());
            }
        }
        $this->entityManager->flush();
    }
    private function handleChargeRefunded(Event $event): void
    {
        $charge = $event->data->object;
        $paymentIntentId = $charge->payment_intent ?? null;
        if (!$paymentIntentId) {
            // Log missing payment intent
            return;
        }
        $order = $this->entityManager->getRepository(Order::class)->findOneBy(['stripePaymentIntentId' => $paymentIntentId]);
        if (!$order) {
            // Log order not found
            return;
        }
        $order->setStatus(OrderStatus::REFUNDED);
        
        $this->entityManager->flush();
    }
}