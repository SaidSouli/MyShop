<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function index(): Response
    {
        $repo = $this->entityManager->getRepository(Order::class);

        return $this->render('admin/dashboard.html.twig', [
            'pending_count'   => count($repo->findBy(
                ['status' => OrderStatus::PENDING]
            )),
            'paid_count'      => count($repo->findBy(
                ['status' => OrderStatus::PAID]
            )),
            'shipped_count'   => count($repo->findBy(
                ['status' => OrderStatus::SHIPPED]
            )),
            'delivered_count' => count($repo->findBy(
                ['status' => OrderStatus::DELIVERED]
            )),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('MyShop Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Catalog');
        yield MenuItem::linkTo(
            ProductCrudController::class, 'Products', 'fa fa-box'
        );
        yield MenuItem::linkTo(
            CategoryCrudController::class, 'Categories', 'fa fa-tag'
        );

        yield MenuItem::section('Sales');
        yield MenuItem::linkTo(
            OrderCrudController::class, 'Orders', 'fa fa-shopping-bag'
        );
        yield MenuItem::linkTo(
            UserCrudController::class, 'Customers', 'fa fa-users'
        );

        yield MenuItem::section('');
        yield MenuItem::linkToUrl(
            'Back to shop', 'fa fa-arrow-left', '/shop'
        );
    }
}