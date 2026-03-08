<?php

namespace App\Service;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;


class CartService {
     private $entityManager;
    private $security;
    private $CartRepository;

    function __construct(EntityManagerInterface $entityManagerInterface , Security   $security, CartRepository $CartRepository) {
        $this->entityManager = $entityManagerInterface;
        $this->security = $security;
        $this->CartRepository = $CartRepository;
    }
    public function getOrCreateCart() : Cart {
        $user = $this->security->getUser();
        if (!$user) {
            throw new \LogicException('User must be logged in to access the cart.');
        }
        
    $cart =$this->CartRepository->findOneBy(['user' => $user]);
    if(!$cart) {
        $cart = new Cart();
        $cart->setUser($user);
        $cart->setCreatedAt(new \DateTimeImmutable());
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($cart);
        $this->entityManager->flush();


    }    return $cart;

    } 
    
    public function addProductTocart($product, $quantity) {
        if ($quantity <= 0) {
        throw new \InvalidArgumentException("Quantity to remove must be greater than zero.");
    }
        $cart = $this->getOrCreateCart();
        $cartItem = $cart->getCartItems()->filter(function($item) use ($product) {
            return $item->getProduct() === $product;
        })->first();
        $currentInCart = ($cartItem) ? $cartItem->getQuantity() : 0;
        $totalRequested = $currentInCart + $quantity;
        if ($totalRequested > $product->getStock()) {
            $remainingStock = $product->getStock() - $currentInCart;
            throw new \LogicException("Not enough stock available. you already have $currentInCart units in your cart. Only $remainingStock units left.");
        }
        if (!$cartItem) {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity($quantity);
            $cart->addCartItem($cartItem);
            $this->entityManager->persist($cartItem);
        } else {
            $cartItem->setQuantity($totalRequested);
        }
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
    public function removeProductFromcart($product, $quantity) {
        if ($quantity <= 0) {
        throw new \InvalidArgumentException("Quantity to remove must be greater than zero.");
    }
        $cart = $this->getOrCreateCart();
        $cartItem = $cart->getCartItems()->filter(function($item) use ($product) {
            return $item->getProduct() === $product;
        })->first();
        if (!$cartItem) {
            throw new \LogicException("Product not found in cart.");
        }
        $currentInCart = $cartItem->getQuantity();
        if ($quantity >= $currentInCart) {
            $cart->removeCartItem($cartItem);
            $this->entityManager->remove($cartItem);
        } else {
            $cartItem->setQuantity($currentInCart - $quantity);
        }
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
    public function calculateTotal(): float {
        $cart = $this->getOrCreateCart();
        $total = 0.0;
        foreach ($cart->getCartItems() as $item) {
            $total += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        return $total;
    }
    public function updateQuantity($product, $quantity) {
        if ($quantity < 0) {
        throw new \InvalidArgumentException("Quantity must be zero or greater.");
    }
        $cart = $this->getOrCreateCart();
        $cartItem = $cart->getCartItems()->filter(function($item) use ($product) {
            return $item->getProduct() === $product;
        })->first();
        if (!$cartItem) {
            throw new \LogicException("Product not found in cart.");
        }
        if ($quantity == 0) {
            $cart->removeCartItem($cartItem);
            $this->entityManager->remove($cartItem);
        } else {
            if ($quantity > $product->getStock()) {
                throw new \LogicException("Not enough stock available. Only {$product->getStock()} units left.");
            }
            $cartItem->setQuantity($quantity);
        }
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    } 
    public function clearCart(): void 
{
    $cart = $this->getOrCreateCart();
    
    foreach ($cart->getCartItems() as $item) {
        
        $this->entityManager->remove($item);
    }

    
    $cart->getCartItems()->clear();
    
    $cart->setUpdatedAt(new \DateTimeImmutable());
    $this->entityManager->flush();
}
}