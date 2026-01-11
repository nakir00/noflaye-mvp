<?php

namespace App\Data\Suppliers;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Supplier Data Transfer Object
 *
 * Encapsulates supplier entity data for validation and transfer between layers.
 * Used for creating, updating, and validating supplier information.
 *
 * @property string $name Supplier's business name
 * @property string $slug URL-friendly identifier
 * @property string|null $description Optional description or notes
 * @property string|null $phone Contact phone number
 * @property string|null $email Contact email address
 * @property string|null $address Business address
 * @property bool $is_active Whether the supplier is active in the system
 */
class SupplierData extends Data
{
    public function __construct(
        #[Required]
        #[Max(255)]
        public string $name,

        #[Required]
        #[Max(255)]
        public string $slug,

        public ?string $description = null,

        #[Max(20)]
        public ?string $phone = null,

        #[Email]
        #[Max(255)]
        public ?string $email = null,

        public ?string $address = null,

        public bool $is_active = true,
    ) {}
}
