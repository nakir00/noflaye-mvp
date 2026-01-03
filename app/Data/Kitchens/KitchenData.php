<?php

namespace App\Data\Kitchens;

use Spatie\LaravelData\Data;

class KitchenData extends Data
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $address = null,
        public bool $is_active = true,
    ) {}
}
