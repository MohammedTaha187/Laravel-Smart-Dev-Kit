<?php

namespace Modules\Commerce\Services;

use Illuminate\Support\Facades\Session;
use App\Models\Product;

class CartService
{
    protected $cart;

    public function __construct()
    {
        $this->cart = Session::get('cart', []);
    }

    public function add(Product $product, int $quantity = 1)
    {
        $id = $product->id;

        if (isset($this->cart[$id])) {
            $this->cart[$id]['quantity'] += $quantity;
        } else {
            $this->cart[$id] = [
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }

        $this->save();
    }

    public function remove(int $id)
    {
        unset($this->cart[$id]);
        $this->save();
    }

    public function total()
    {
        return collect($this->cart)->sum(fn($item) => $item['price'] * $item['quantity']);
    }

    protected function save()
    {
        Session::put('cart', $this->cart);
    }
}
