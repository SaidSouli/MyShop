<?php

namespace App\Controller;

use App\Entity\Product;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart')]
final class CartController extends AbstractController
{
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    #[Route('', name: 'app_cart')]
    public function index(): Response
    {
        $cart = $this->cartService->getOrCreateCart();

        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'cart' => $cart,
            'total' => $this->cartService->calculateTotal(),
        ]);
    }

    #[Route('/getOrCreate', name: 'app_getorcreate_cart')]
    public function getOrCreate(): Response
    {
        $cart = $this->cartService->getOrCreateCart();
        dd($cart);
    }

    #[Route('/addProduct/{Id}', name: 'app_addProduct_cart')]
    public function addProduct(Product $Id, CartService $cartService): Response
    {
        try {
            $cartService->addProductTocart($Id, 1);
            $this->addFlash('success', 'Product added to cart successfully!');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/removeProduct/{Id}', name: 'app_removeProduct_cart')]
    public function removeProduct(Product $Id, CartService $cartService): Response
    {
        try {
            $cartService->removeProductFromcart($Id, 1);
            $this->addFlash('success', 'Product removed from cart successfully!');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/calculateTotal', name: 'app_calculate_total_cart')]
    public function calculateTotal(CartService $cartService): Response
    {
        $total = $cartService->calculateTotal();
        $this->addFlash('success', "Total cart value: $total");

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/updateQuantity/{Id}/{quantity}', name: 'app_update_quantity_cart')]
    public function updateQuantity(Product $Id, int $quantity, CartService $cartService): Response
    {
        try {
            $cartService->updateQuantity($Id, $quantity);
            $this->addFlash('success', 'Cart updated successfully!');
        } catch (\LogicException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/clearCart', name: 'app_clear_cart')]
    public function clearCart(CartService $cartService): Response
    {
        $cartService->clearCart();
        $this->addFlash('success', 'Cart cleared successfully!');

        return $this->redirectToRoute('app_cart');
    }
}
