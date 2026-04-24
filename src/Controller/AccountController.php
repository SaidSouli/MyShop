<?php

namespace App\Controller;


use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/account', name: 'app_account')]
#[IsGranted('ROLE_USER')]
final class AccountController extends AbstractController
{
    
public function __construct(
        private EntityManagerInterface $entityManager,private OrderRepository $orderRepository
    ) {
    }
    #[Route('', name: '_index', methods: ['GET'])]
    public function index(): Response
    {
       $user = $this->getUser();
       $recentOrders = $this->orderRepository->findRecentByUser($user, 5);
        return $this->render('account/index.html.twig', [
            'user' => $user,
            'recentOrders' => $recentOrders
        ]);
    }
    // ─────────────────────────────────────────
    // GET /account/orders - full orders list
    // ─────────────────────────────────────────
    #[Route('/orders', name: '_orders', methods: ['GET'])]
    public function orders(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $orders = $this->orderRepository->findByUser($user);
        return $this->render('account/orders.html.twig', [
            'orders' => $orders
        ]);
    }
    // ─────────────────────────────────────────
    // GET /account/orders/{reference} - order details
    // ─────────────────────────────────────────
    #[Route('/orders/{reference}', name: '_order_detail', methods: ['GET'])]
    public function orderDetails(string $reference): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $order = $this->orderRepository->findOneBy([
            'reference' => $reference
        ]);
        if (!$order ) {
            throw $this->createNotFoundException('Order not found.');
        }
        if ($order->getCustomer() !== $user) {
            throw $this->createAccessDeniedException('You do not have access to this order.');
        }
        return $this->render('account/order_details.html.twig', [
            'order' => $order
        ]);
    }

}