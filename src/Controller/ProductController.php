<?php

namespace App\Controller;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProductController extends AbstractController
{
    #[Route('/shop', name: 'app_shop_index', methods: ['GET'])]
    public function index( Request $request , PaginatorInterface $paginator , ProductRepository $productRepository , CategoryRepository $categoryRepository ): Response
    {
        $categorySlug = $request->query->get('category');
        $searchTerm = $request->query->get('q');
        $page = $request->query->getInt('page', 1);
        
        $categories = $categoryRepository->findAll();

        $currentCategory = null;
        if ($categorySlug) {
            $currentCategory = $categoryRepository->findOneBy(['slug' => $categorySlug]);
        }
        $queryBuilder = $productRepository->findFiltered( $currentCategory , $searchTerm );
         $products = $paginator->paginate(
            $queryBuilder,
            $page,
            12
        );

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'searchTerm' => $searchTerm,
            
        ]);
    }

    #[Route('/product/{slug}', name: 'app_product_show', methods: ['GET'])]
    public function show(string $slug, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findActiveBySlug($slug);

        if (!$product) {
            throw $this->createNotFoundException(sprintf('Product with slug "%s" not found.', $slug));
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
