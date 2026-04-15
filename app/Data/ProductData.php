<?php

namespace App\Data;

use App\Data\BaseData;
use Spatie\LaravelData\Attributes\Validation\Rule;

class ProductData extends BaseData
{
    public function __construct(
        #[Rule('required|string|max:255')]
        public string $name,

        #[Rule('required|numeric|min:0')]
        public float $price,

        #[Rule('nullable|string')]
        public ?string $description,
    ) {}
}
