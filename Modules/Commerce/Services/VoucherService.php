<?php

namespace Modules\Commerce\Services;

class VoucherService
{
    public function apply(string $code, float $total)
    {
        // Simple mock logic for vouchers
        if ($code === 'PROMO10') {
            return $total * 0.9;
        }

        return $total;
    }
}
