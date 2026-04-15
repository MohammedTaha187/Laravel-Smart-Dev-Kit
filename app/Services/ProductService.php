<?php

namespace App\Services;

use App\Contracts\ProductServiceInterface;
use App\Models\Product;
use App\Data\ProductData;

class ProductService extends BaseService implements ProductServiceInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function all()
    {
        return Product::all();
    }

    public function create(array $data)
    {
        return Product::create($data);
    }
}
