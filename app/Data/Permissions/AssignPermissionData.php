<?php

namespace App\Data\Permissions;

use App\Enums\Permission as PermissionEnum;
use Carbon\Carbon;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

/**
 * Data Transfer Object for assigning permissions to users
 *
 * This DTO encapsulates all the data needed to assign a permission to a user,
 * including scope, temporal validity, source tracking, and optional reasoning.
 * Used in conjunction with AssignPermissionToUser action.
 *
 * @property int $user_id The ID of the user receiving the permission
 * @property PermissionEnum $permission The permission enum case being assigned
 * @property int|null $scope_id Optional scope ID for scoped permissions (e.g., specific shop/kitchen)
 * @property Carbon|null $valid_from When the permission becomes active (null = immediately)
 * @property Carbon|null $valid_until When the permission expires (null = never)
 * @property string $source How the permission was granted ('direct', 'template', 'delegation', 'import')
 * @property string|null $reason Optional explanation for audit trail
 */
class AssignPermissionData extends Data
{
    public function __construct(
        #[Required]
        public int $user_id,

        #[Required]
        #[WithCast(EnumCast::class)]
        public PermissionEnum $permission,

        public ?int $scope_id = null,

        public ?Carbon $valid_from = null,

        public ?Carbon $valid_until = null,

        public string $source = 'direct',

        public ?string $reason = null,
    ) {}

    /**
     * Get the permission slug as a string
     *
     * Convenience method to extract the slug value from the permission enum.
     * Useful when interfacing with database queries or external systems.
     *
     * @return string The permission slug (e.g., 'users.view', 'shops.create')
     *
     * @example
     * $data->permissionSlug(); // Returns 'users.view' if permission is Permission::USER_VIEW
     */
    public function permissionSlug(): string
    {
        return $this->permission->value;
    }
}
