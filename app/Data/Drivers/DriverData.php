<?php

namespace App\Data\Drivers;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Driver Data Transfer Object
 *
 * Encapsulates driver entity data for validation and transfer between layers.
 * Used for creating, updating, and validating driver information.
 *
 * @property string $name Driver's full name
 * @property string $slug URL-friendly identifier
 * @property string|null $description Optional description or notes
 * @property string|null $phone Contact phone number
 * @property string|null $email Contact email address
 * @property string|null $vehicle_type Type of vehicle (van, motorcycle, car, etc.)
 * @property string|null $vehicle_number Vehicle registration/license plate
 * @property string|null $license_number Driver's license number
 * @property bool $is_active Whether the driver is active in the system
 * @property bool $is_available Whether the driver is currently available for assignments
 */
class DriverData extends Data
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

        #[Max(100)]
        public ?string $vehicle_type = null,

        #[Max(50)]
        public ?string $vehicle_number = null,

        #[Max(50)]
        public ?string $license_number = null,

        public bool $is_active = true,

        public bool $is_available = true,
    ) {}

    
}
