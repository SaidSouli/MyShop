<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::PROCESSING => 'Processing',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    /**
     * Get description/meaning of each status
     */
    public function description(): string
    {
        return match($this) {
            self::PENDING => 'Order created, payment not yet confirmed',
            self::PAID => 'Payment confirmed via Stripe webhook',
            self::PROCESSING => 'Being prepared for shipment',
            self::SHIPPED => 'Dispatched, tracking available',
            self::DELIVERED => 'Confirmed received',
            self::CANCELLED => 'Cancelled before shipment',
            self::REFUNDED => 'Payment returned to customer',
        };
    }

    /**
     * Check if status is terminal (no further changes)
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::DELIVERED, self::CANCELLED, self::REFUNDED]);
    }

    /**
     * Check if payment is confirmed
     */
    public function isPaymentConfirmed(): bool
    {
        return in_array($this, [self::PAID, self::PROCESSING, self::SHIPPED, self::DELIVERED]);
    }

    /**
     * Get next allowed statuses
     */
    public function getAllowedTransitions(): array
    {
        return match($this) {
            self::PENDING => [self::PAID, self::CANCELLED],
            self::PAID => [self::PROCESSING, self::REFUNDED],
            self::PROCESSING => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::DELIVERED, self::REFUNDED],
            self::DELIVERED => [self::REFUNDED],
            self::CANCELLED => [],
            self::REFUNDED => [],
        };
    }

    /**
     * Get color for UI (Bootstrap/Tailwind classes)
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',    // yellow/orange
            self::PAID => 'info',          // blue
            self::PROCESSING => 'primary',  // dark blue
            self::SHIPPED => 'info',        // light blue
            self::DELIVERED => 'success',   // green
            self::CANCELLED => 'danger',    // red
            self::REFUNDED => 'secondary',  // gray
        };
    }

    /**
     * Get icon for UI (FontAwesome/Icon class names)
     */
    public function icon(): string
    {
        return match($this) {
            self::PENDING => 'fa-clock',
            self::PAID => 'fa-credit-card',
            self::PROCESSING => 'fa-cogs',
            self::SHIPPED => 'fa-truck',
            self::DELIVERED => 'fa-check-circle',
            self::CANCELLED => 'fa-times-circle',
            self::REFUNDED => 'fa-undo',
        };
    }

    /**
     * Get all statuses as array for forms
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::cases() as $case) {
            $choices[$case->label()] = $case->value;
        }
        return $choices;
    }

    /**
     * Get status from value (safe)
     */
    public static function tryFromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }
}