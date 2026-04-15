<?php

namespace Modules\Commerce\Services;

use App\Models\Order;
use Modules\Commerce\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function placeOrder(array $cartItems, int $userId, array $address)
    {
        return DB::transaction(function () use ($cartItems, $userId, $address) {
            $total = collect($cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);

            // Create Order
            $order = Order::create([
                'user_id' => $userId,
                'total' => $total,
                'status' => OrderStatus::PENDING,
                'shipping_address' => $address,
            ]);

            // Create Order Items logic would go here
            
            return $order;
        });
    }

    public function updateStatus(Order $order, OrderStatus $status)
    {
        $order->update(['status' => $status]);
        
        // Trigger event/notification logic would go here
        
        return $order;
    }
}
