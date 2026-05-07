<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Order')
            ->setEntityLabelInPlural('Orders')
            ->setPageTitle('index', 'Order Management')
            ->setPageTitle(
                'detail',
                fn(Order $o) => sprintf(
                    'Order %s', $o->getReference()
                )
            )
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    // ─────────────────────────────────────────
    // Fields
    // ─────────────────────────────────────────

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference', 'Reference');

        yield TextField::new('customerEmail', 'Customer email')
            ->hideOnForm();

        yield ChoiceField::new('status', 'Status')
            ->setChoices([
                'Pending'    => OrderStatus::PENDING,
                'Paid'       => OrderStatus::PAID,
                'Processing' => OrderStatus::PROCESSING,
                'Shipped'    => OrderStatus::SHIPPED,
                'Delivered'  => OrderStatus::DELIVERED,
                'Cancelled'  => OrderStatus::CANCELLED,
                'Refunded'   => OrderStatus::REFUNDED,
            ])
            ->renderAsBadges([
                OrderStatus::PENDING->value    => 'warning',
                OrderStatus::PAID->value       => 'success',
                OrderStatus::PROCESSING->value => 'primary',
                OrderStatus::SHIPPED->value    => 'info',
                OrderStatus::DELIVERED->value  => 'success',
                OrderStatus::CANCELLED->value  => 'danger',
                OrderStatus::REFUNDED->value   => 'danger',
            ]);

        yield MoneyField::new('total', 'Total')
            ->setCurrency('USD')
            ->setStoredAsCents(false);

        yield TextField::new('shippingAddress', 'Address')
            ->hideOnIndex();

        yield TextField::new('shippingCity', 'City')
            ->hideOnIndex();

        yield TextField::new('shippingPostcode', 'Postcode')
            ->hideOnIndex();

        yield TextField::new('shippingCountry', 'Country')
            ->hideOnIndex();

        yield TextField::new('note', 'Customer note')
            ->hideOnIndex()
            ->hideOnForm();

        yield TextField::new('stripePaymentIntentId', 'Stripe PI')
            ->hideOnIndex()
            ->hideOnForm()
            ->setHelp(
                'Use this to look up the payment in Stripe dashboard'
            );

        yield DateTimeField::new('createdAt', 'Placed at')
            ->setFormat('dd MMM yyyy, HH:mm')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt', 'Last updated')
            ->setFormat('dd MMM yyyy, HH:mm')
            ->hideOnIndex()
            ->hideOnForm();
    }

    // ─────────────────────────────────────────
    // Filters
    // ─────────────────────────────────────────

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status')->setChoices([
                'Pending'    => OrderStatus::PENDING,
                'Paid'       => OrderStatus::PAID,
                'Processing' => OrderStatus::PROCESSING,
                'Shipped'    => OrderStatus::SHIPPED,
                'Delivered'  => OrderStatus::DELIVERED,
                'Cancelled'  => OrderStatus::CANCELLED,
                'Refunded'   => OrderStatus::REFUNDED,
            ]))
            ->add(DateTimeFilter::new('createdAt', 'Order date'));
    }

    // ─────────────────────────────────────────
    // Actions
    // ─────────────────────────────────────────

    public function configureActions(Actions $actions): Actions
    {
        $markProcessing = Action::new(
            'markProcessing',
            'Mark Processing',
            'fa fa-cog'
        )
            ->linkToCrudAction('markProcessing')
            ->displayIf(
                fn(Order $o) =>
                    $o->getStatus() === OrderStatus::PAID
            );

        $markShipped = Action::new(
            'markShipped',
            'Mark Shipped',
            'fa fa-truck'
        )
            ->linkToCrudAction('markShipped')
            ->displayIf(
                fn(Order $o) =>
                    $o->getStatus() === OrderStatus::PROCESSING
            );

        $markDelivered = Action::new(
            'markDelivered',
            'Mark Delivered',
            'fa fa-check'
        )
            ->linkToCrudAction('markDelivered')
            ->displayIf(
                fn(Order $o) =>
                    $o->getStatus() === OrderStatus::SHIPPED
            );

        $markCancelled = Action::new(
            'markCancelled',
            'Cancel order',
            'fa fa-times'
        )
            ->linkToCrudAction('markCancelled')
            ->addCssClass('text-danger')
            ->displayIf(fn(Order $o) => in_array(
                $o->getStatus(),
                [OrderStatus::PENDING, OrderStatus::PAID]
            ));

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $markProcessing)
            ->add(Crud::PAGE_INDEX, $markShipped)
            ->add(Crud::PAGE_INDEX, $markDelivered)
            ->add(Crud::PAGE_INDEX, $markCancelled)
            ->add(Crud::PAGE_DETAIL, $markProcessing)
            ->add(Crud::PAGE_DETAIL, $markShipped)
            ->add(Crud::PAGE_DETAIL, $markDelivered)
            ->add(Crud::PAGE_DETAIL, $markCancelled)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    // ─────────────────────────────────────────
    // Custom action handlers
    // EasyAdmin 5 passes AdminContext directly
    // ─────────────────────────────────────────

    public function markProcessing(
        AdminContext           $context,
        AdminUrlGenerator      $adminUrlGenerator,
        EntityManagerInterface $em
    ): RedirectResponse {
        return $this->updateOrderStatus(
            $context,
            $adminUrlGenerator,
            $em,
            OrderStatus::PROCESSING
        );
    }

    public function markShipped(
        AdminContext           $context,
        AdminUrlGenerator      $adminUrlGenerator,
        EntityManagerInterface $em
    ): RedirectResponse {
        return $this->updateOrderStatus(
            $context,
            $adminUrlGenerator,
            $em,
            OrderStatus::SHIPPED
        );
    }

    public function markDelivered(
        AdminContext           $context,
        AdminUrlGenerator      $adminUrlGenerator,
        EntityManagerInterface $em
    ): RedirectResponse {
        return $this->updateOrderStatus(
            $context,
            $adminUrlGenerator,
            $em,
            OrderStatus::DELIVERED
        );
    }
    #[AdminRoute]
    public function markCancelled(
        AdminContext           $context,
        AdminUrlGenerator      $adminUrlGenerator,
        EntityManagerInterface $em
    ): RedirectResponse {
        return $this->updateOrderStatus(
            $context,
            $adminUrlGenerator,
            $em,
            OrderStatus::CANCELLED
        );
    }

    // ─────────────────────────────────────────
    // Shared update logic
    // ─────────────────────────────────────────

    private function updateOrderStatus(
        AdminContext           $context,
        AdminUrlGenerator      $adminUrlGenerator,
        EntityManagerInterface $em,
        OrderStatus            $newStatus
    ): RedirectResponse {

        // EasyAdmin 5 — get the entity from context directly
        /** @var Order $order */
        $order = $context->getEntity()->getInstance();

        if ($order) {
            $order->setStatus($newStatus);
            $em->flush();

            $this->addFlash(
                'success',
                sprintf(
                    'Order %s marked as %s.',
                    $order->getReference(),
                    $newStatus->value
                )
            );
        }

        // Redirect back to the order index
        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}