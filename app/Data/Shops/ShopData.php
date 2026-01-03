<?php

namespace App\Data\Shops;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class ShopData extends Data
{
    public function __construct(
        #[Required]
        public string $name,

        #[Required]
        public string $slug,

        public ?string $description = null,

        public ?string $address = null,

        public ?string $phone = null,

        public bool $is_active = true,
    ) {}
}
