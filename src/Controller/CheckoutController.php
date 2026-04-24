<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItems;
use App\Enum\OrderStatus;
use App\Form\CheckoutType;
use App\Service\CartService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout', name: 'app_checkout_')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private CartService            $cartService,
        private EntityManagerInterface $entityManager,
        private MailerService          $mailerService
    ) {}

    // ─────────────────────────────────────────
    // GET+POST /checkout — show and handle form
    // ─────────────────────────────────────────

    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        // 1. Guard: redirect to cart if empty
        if ($this->cartService->isEmpty()) {
            $this->addFlash('error', 'Your cart is empty.');
            return $this->redirectToRoute('app_cart_index');
        }

        // 2. Pre-fill form data from the logged-in user
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $formData = [
            'firstName' => $user->getFirstName(),
            'lastName'  => $user->getLastName(),
            'address'   => $user->getAddress(),
        ];

        // 3. Build and handle the form
        $form = $this->createForm(CheckoutType::class, $formData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // 4. Create the Order entity
            $order = new Order();
            $order->setCustomer($user);
            $order->setStatus(OrderStatus::PENDING);
            $order->setShippingAddress($data['address']);
            $order->setShippingCity($data['city']);
            $order->setShippingPostcode($data['postcode']);
            $order->setShippingCountry($data['country']);
            $order->setNote($data['note'] ?? null);

            // 5. Create OrderItems from cart
            $total = 0;

            foreach ($this->cartService->getItems() as $item) {
                $orderItem = new OrderItems();
                $orderItem->setProduct($item['product']);
                $orderItem->setQuantity($item['quantity']);

                // ← The price snapshot moment
                $orderItem->setUnitPrice($item['product']->getPrice());
                $orderItem->setAnOrder($order);
                $order->getOrderItems()->add($orderItem);
                $this->entityManager->persist($orderItem);

                $total += $item['product']->getPrice()
                        * $item['quantity'];

                // 6. Decrement product stock
                $item['product']->setStock(
                    $item['product']->getStock() - $item['quantity']
                );
            }

            // 7. Set the total
            $order->setTotal((string) $total);

            // 8. Persist everything
            // Order cascades to OrderItems automatically
            $this->entityManager->persist($order);
            $this->entityManager->flush();

            // 9. Clear the cart
            $this->cartService->clear();

            // 10. Send confirmation email
            try {
                $this->mailerService->sendOrderConfirmation($order);
            } catch (\Exception $e) {
                // Don't fail the order if the email fails
                // Log the error in production
            }

            // 11. Redirect to confirmation page
            return $this->redirectToRoute('app_checkout_confirmation', [
                'reference' => $order->getReference(),
            ]);
        }

        // 12. Render the checkout form
        return $this->render('checkout/index.html.twig', [
            'form'  => $form,
            'items' => $this->cartService->getItems(),
            'total' => $this->cartService->getTotal(),
        ]);
    }

    // ─────────────────────────────────────────
    // GET /checkout/confirmation/{reference}
    // ─────────────────────────────────────────

    #[Route('/confirmation/{reference}', name: 'confirmation', methods: ['GET'])]
    public function confirmation(string $reference): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        // Find the order by reference
        $order = $this->entityManager
            ->getRepository(Order::class)
            ->findOneBy(['reference' => $reference]);

        // 404 if not found
        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        // Security: only the owner can view their confirmation
        if ($order->getCustomer() !== $user) {
            throw $this->createAccessDeniedException(
                'You cannot view this order.'
            );
        }

        return $this->render('checkout/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}