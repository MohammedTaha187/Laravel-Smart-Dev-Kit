<?php

namespace App\Contracts;

use App\Data\ProductData;

interface ProductServiceInterface
{
    public function all();
    public function create(array $data);
}
