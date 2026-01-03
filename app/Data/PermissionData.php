<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class PermissionData extends Data
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public int $permission_group_id,
        public bool $is_active = true,
        public bool $is_system = false,
    ) {}
}
