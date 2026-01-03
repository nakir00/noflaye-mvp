<?php

namespace App\Actions;

use App\Models\Permission;
use App\Models\Scope;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class RevokePermissionFromUser
{
    use AsAction;

    public function handle(User $user, Permission $permission, ?Scope $scope = null): bool
    {
        $detached = $user->permissions()
            ->wherePivot('permission_id', $permission->id)
            ->wherePivot('scope_id', $scope?->id)
            ->detach();

        if ($detached) {
            activity()
                ->performedOn($user)
                ->causedBy(Auth::user())
                ->withProperties([
                    'permission' => $permission->slug,
                    'scope_id' => $scope?->id,
                ])
                ->log("Permission '{$permission->name}' revoked from user");

            Cache::tags(['users', "user.{$user->id}", 'permissions'])->flush();
        }

        return $detached > 0;
    }
}
