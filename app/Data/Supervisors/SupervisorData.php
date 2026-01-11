<?php

namespace App\Data\Supervisors;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * Supervisor Data Transfer Object
 *
 * Encapsulates supervisor entity data for validation and transfer between layers.
 * Used for creating, updating, and validating supervisor information.
 *
 * @property string $name Supervisor's full name
 * @property string $slug URL-friendly identifier
 * @property string|null $description Optional description or notes
 * @property string|null $phone Contact phone number
 * @property string|null $email Contact email address
 * @property string|null $address Physical address
 * @property bool $is_active Whether the supervisor is active in the system
 */
class SupervisorData extends Data
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
