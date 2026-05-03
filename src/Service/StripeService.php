<?php

namespace App\Service;

use App\Entity\Order;
use Stripe\Checkout\Session;
use Stripe\Stripe;
class StripeService
{
    public function __construct(private string $stripeSecretKey , private string $stripePublicKey , private string $appBaseUrl)
    {
        Stripe::setApiKey($this->stripeSecretKey);
    }    
    /**
     * Create a Stripe checkout session for order.
     * Returns the Stripe-hosted payment URL to redirect the user to.
     */
    public function createCheckoutSession(Order $order): Session
    {   
        $lineItems = [];
        foreach ($order->getOrderItems() as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => (int) round($item->getUnitPrice() * 100), // Convert to cents
                    'product_data' => [
                        'name' => $item->getproductName(),
                    ],
                    ],
                'quantity' => $item->getQuantity(),
            ];
        }

        return $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'metadata' => [
                'order_reference' => $order->getReference(),
            ],
            'success_url' => $this->appBaseUrl . '/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->appBaseUrl . '/payment/cancel?reference='.$order->getReference(),

            'customer_email' => $order->getCustomer()->getEmail(),
            ]);

        
    }
    public function getStripePublicKey(): string
    {
        return $this->stripePublicKey;
    }
}