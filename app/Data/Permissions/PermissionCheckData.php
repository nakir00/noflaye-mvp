<?php

namespace App\Data\Permissions;

use App\Enums\Permission as PermissionEnum;
use Spatie\LaravelData\Data;

class PermissionCheckData extends Data
{
    public function __construct(
        public int $user_id,
        public PermissionEnum $permission,
        public ?int $scope_id = null,
        public array $context = [],
    ) {}
}
