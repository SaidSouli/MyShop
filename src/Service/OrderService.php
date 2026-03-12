<?php

namespace App\Service;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Entity\Order;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\OrderRepository;

class OrderService
{
    private $entityManager;
    private $security;
    private $orderRepository;
    private $cartService;

    public function __construct(EntityManagerInterface $entityManager, Security $security, OrderRepository $orderRepository, CartService $cartService)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
        $this->orderRepository = $orderRepository;
        $this->cartService = $cartService;
    }

    public function createOrder (User $user): Order 
    {
        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setStatus("pending");

        $total = 0;
        $cart = $this->cartService->getOrCreateCart();
        foreach ($cart->getCartItems() as $cartItem) {
            $orderitem = new OrderItem();
            $orderitem->setProduct($cartItem->getProduct());
            $orderitem->setQuantity($cartItem->getQuantity());
            $orderitem->setPrice($cartItem->getProduct()->getPrice());
            $order->addOrderItem($orderitem);
            
            $total += $orderitem->getPrice() * $orderitem->getQuantity();
        }
        $order->setTotal($total);

        $this->entityManager->persist($order);
        $this->entityManager->flush();
        return $order;
    }
}