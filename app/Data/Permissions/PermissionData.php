<?php

namespace App\Data\Permissions;

use App\Enums\Permission as PermissionEnum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class PermissionData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,

        #[Required, StringType, Max(255)]
        public string $slug,

        public ?string $description,

        #[Required]
        public int $permission_group_id,

        public bool $is_active = true,

        public bool $is_system = false,
    ) {}

    /**
     * Create from enum
     */
    public static function fromEnum(PermissionEnum $permission, int $permissionGroupId = 1): self
    {
        return new self(
            name: ucfirst(str_replace(['.', '_'], ' ', $permission->value)),
            slug: $permission->value,
            description: "Permission to {$permission->action()} {$permission->resource()}",
            permission_group_id: $permissionGroupId,
            is_active: true,
            is_system: true,
        );
    }
}
