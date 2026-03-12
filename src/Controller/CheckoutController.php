<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\OrderService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
#[Route('/checkout')]
final class CheckoutController extends AbstractController
{
    private $stripeSecretKey;
    private $entityManager;
    private $cartService;
    private $security;
    private $orderService;
    public function __construct(#[Autowire('%env(STRIPE_SECRET_KEY)%')] string $stripeSecretKey, EntityManagerInterface $entityManager, CartService $cartService, Security $security, OrderService $orderService)
    {
        $this->stripeSecretKey = $stripeSecretKey;
        $this->entityManager = $entityManager;
        $this->cartService = $cartService;
        $this->security = $security;
        $this->orderService = $orderService;
    }
    #[Route('', name: 'app_checkout')]
    public function index(): Response
    {
        
        return $this->render('checkout/index.html.twig', [
            'controller_name' => 'CheckoutController',
        ]);
    }
    #[Route('/confirm', name:'app_checkout_prepare')]
    public function prepareOrder(): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \LogicException('User must be logged in to access the cart.');
        }
        $order=$this->orderService->createOrder($user);
        
        return $this->redirectToRoute('app_checkout_pay', ['id' => $order->getId()]);
    }

    #[Route('pay/{id}', name:'app_checkout_pay')]
    public function pay (Order $order): Response 
    {
 
        $stripe = new \Stripe\StripeClient(trim($this->stripeSecretKey));
        $lineItems = [];
        foreach ($order->getOrderItems() as $item) 
        {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $item->getProduct()->getName(),
                    ],
                    'unit_amount' =>(int) ($item->getPrice() * 100),
                ],
                'quantity' =>(int) ($item->getQuantity()),
            ];
        }
        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_checkout_success', ['id' => $order->getId()],  UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_checkout_cancel', ['id' => $order->getId()],  UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
        return $this->redirect($session->url, 303);
    
}
        #[Route('success/{id}', name:'app_checkout_success')]
        public function success (Order $order): Response
        {
            if ($order->getStatus() === "paid") {
                return $this->redirectToRoute('app_home');
            }
            foreach ($order->getOrderItems() as $item) {
                $product = $item->getProduct();
                $product->setStock($product->getStock() - $item->getQuantity());
                
                
            }
            $this->entityManager->persist($order->setStatus("paid"));
            
            $this->cartService->clearCart();
            $this->entityManager->flush();
            return $this->render('checkout/success.html.twig', [
                'order' => $order,
            ]);
        }
        #[Route('/cancel', name:'app_checkout_cancel')]
        public function cancel(Order $order): Response
        {
            $this->addFlash('error', 'Payment cancelled. ');
            return $this->redirectToRoute('app_cart');
        }
}