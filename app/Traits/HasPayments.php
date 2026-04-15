<?php

namespace App\Traits;

/**
 * Trait HasPayments
 * 
 * Note: This trait is prepared for Laravel Cashier (Stripe).
 * Once laravel/cashier is installed and compatible with your Laravel version,
 * you can add 'use Billable;' here.
 */
trait HasPayments
{
    // use Billable;

    public function isPremium(): bool
    {
        // Placeholder logic for subscription check
        return false;
    }
}
