<?php

namespace App\Data\Permissions;

use App\Enums\Permission as PermissionEnum;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

/**
 * Data Transfer Object for revoking permissions from users
 *
 * This DTO encapsulates all the data needed to revoke a permission from a user.
 * Supports both global and scoped permission revocation with optional audit reasoning.
 * Used in conjunction with RevokePermissionFromUser action.
 *
 * @property int $user_id The ID of the user losing the permission
 * @property PermissionEnum $permission The permission enum case being revoked
 * @property int|null $scope_id Optional scope ID to revoke permission for specific scope only
 * @property string|null $reason Optional explanation for audit trail
 */
class RevokePermissionData extends Data
{
    public function __construct(
        #[Required]
        public int $user_id,

        #[Required]
        #[WithCast(EnumCast::class)]
        public PermissionEnum $permission,

        public ?int $scope_id = null,

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
     * $data->permissionSlug(); // Returns 'users.delete' if permission is Permission::USER_DELETE
     */
    public function permissionSlug(): string
    {
        return $this->permission->value;
    }
}
