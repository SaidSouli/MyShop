<?php

namespace App\Controller;

use App\Service\CartService;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/cart', name: 'app_cart_')]
final class CartController extends AbstractController
{
    
    public function __construct(
        private CartService $cartService
    ) {
    }

   

#[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('cart/index.html.twig', [
            'items' => $this->cartService->getItems(),
            'total' => $this->cartService->getTotal()

        ]);
    }

    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    public function add(int $id, Request $request, ProductRepository $productRepository): Response
    {
        
        if (!$this->isCsrfTokenValid('add-to-cart' , $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid Request.');
            return $this->redirectToRoute('app_shop_index');
        }
        $product = $productRepository->find($id);

        if (!$product || !$product->isActive()) {
            $this->addFlash('error', 'Product not found.');
            return $this->redirectToRoute('app_shop_index');
        }

        $quantity = (int) $request->request->get('quantity', 1);
        
        if ($quantity <= 0) {
            $this->addFlash('error', 'quantity must be atleast 1.');
            return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]);
        }
        
        if ($product->getStock()===0) {
            $this->addFlash('error', 'Product is out of stock.');
            return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]);
        }

        
        $this->cartService->add($product->getId(), $quantity);
        $this->addFlash('success', sprintf('""%s" added to cart.', $product->getName()));

        return $this->redirectToRoute('app_product_show', ['slug' => $product->getSlug()]   );
    }
    #[Route('/remove/{id}', name: 'remove', methods: ['POST'])]
    public function remove(int $id, Request $request, ): Response
    {
        if (!$this->isCsrfTokenValid('remove_from_cart' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid Request.');
            return $this->redirectToRoute('app_cart_index');
        }
        $this->cartService->remove($id);
        $this->addFlash('success', 'Product removed from cart.');

        return $this->redirectToRoute('app_cart_index');
    }

    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('update_cart' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid Request.');
            return $this->redirectToRoute('app_cart_index');
        }
        $quantity = (int) $request->request->get('quantity', 1);
        
            $this->cartService->updateQuantity($id, $quantity);
            
        

        return $this->redirectToRoute('app_cart_index');
    }
    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('clear_cart', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid Request.');
            return $this->redirectToRoute('app_cart_index');
        }
        $this->cartService->clear();
        $this->addFlash('success', 'Cart cleared successfully.');

        return $this->redirectToRoute('app_cart_index');
    }
}