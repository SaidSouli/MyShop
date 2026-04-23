<?php

namespace App\Service;


use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface as Session;

class CartService
{
    private RequestStack $requestStack;
    private ProductRepository $productRepository;
    private const CART_KEY = 'cart';
    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }
    private function getSession(): Session
    {
        return $this->requestStack->getSession();
    }

    private function getRawCart(): array
    {
        return $this->getSession()->get(self::CART_KEY, []);
    }

    private function saveCart(array $cart): void
    {
        $this->getSession()->set(self::CART_KEY, $cart);
    }


    

    public function add(int $productId, int $quantity = 1): void
    {
        $cart = $this->getRawCart();
        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }
        $product = $this->productRepository->find($productId);
        if ($product && $cart[$productId] > $product->getStock()) {
            $cart[$productId] = $product->getStock();
        }
        if ($cart[$productId] <= 0) {
            unset($cart[$productId]);
        }


        $this->saveCart($cart);
    }
    public function remove(int $productId): void
    {
        $cart = $this->getRawCart();
        unset($cart[$productId]);
        $this->saveCart($cart);
    }
    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = $this->getRawCart();
        if ($quantity <= 0) {
            $this->remove($productId);
            return;
        } 
        if (!isset($cart[$productId])) {
            return; 
        }

        $product = $this->productRepository->find($productId);
        if ($product)
            {
               $quantity = min($quantity, $product->getStock()); 
            }
        $cart[$productId] = $quantity;
        
        $this->saveCart($cart);
    }

    public function clear() : void
    {
        $this->getSession()->remove(self::CART_KEY);
    }
    public function getItems(): array
    {
        $cart = $this->getRawCart();
        $products = $this->productRepository->findByIds(array_keys($cart));
        $items = [];
        $dirty = false;
        foreach ($cart as $productId => $quantity) {
            if (!isset($products[$productId])) {
               unset($cart[$productId]);
                $dirty = true;
                continue;
            }
            $product = $products[$productId];
            if ($quantity > $product->getStock()) {
                $quantity = $product->getStock();
                $cart[$productId] = $quantity;
                $dirty = true;
            }
            $items[] = [
                'product' => $product,
                'quantity' => $quantity,
                'subtotal' => $product->getPrice() * $quantity,
            ];
        }
        if ($dirty) {
            $this->saveCart($cart);
        }
        return $items;
    }
    public function getTotal(): float
    {
        $items = $this->getItems();
        $total = 0.0;
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        return $total;
    }
    public function getCount(): int
    {
        $cart = $this->getRawCart();
        return array_sum($cart);
    }
    public function isEmpty(): bool
    {
        $cart = $this->getRawCart();
        return empty($cart);
    }
}