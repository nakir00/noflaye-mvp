<?php

namespace App\Actions;

use App\Models\Permission;
use App\Models\Scope;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\Concerns\AsAction;

class AssignPermissionToUser
{
    use AsAction;

    /**
     * Assign a permission to a user
     *
     * @param User $user
     * @param Permission $permission
     * @param Scope|null $scope
     * @return bool
     */
    public function handle(User $user, Permission $permission, ?Scope $scope = null): bool
    {
        // Check if already assigned
        $exists = $user->permissions()
            ->where('permission_id', $permission->id)
            ->where('scope_id', $scope?->id)
            ->exists();

        if ($exists) {
            return false;
        }

        // Attach permission
        $user->permissions()->attach($permission->id, [
            'scope_id' => $scope?->id,
            'source' => 'direct',
            'granted_at' => now(),
        ]);

        // Log activity
        activity()
            ->performedOn($user)
            ->causedBy(Auth::user())
            ->withProperties([
                'permission' => $permission->slug,
                'permission_name' => $permission->name,
                'scope_id' => $scope?->id,
            ])
            ->log("Permission '{$permission->name}' granted to user");

        // Clear cache
        Cache::tags(['users', "user.{$user->id}", 'permissions'])->flush();

        return true;
    }

    /**
     * Use as controller
     */
    public function asController()
    {
        $validated = request()->validate([
            'user_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id',
            'scope_id' => 'nullable|exists:scopes,id',
        ]);

        $result = $this->handle(
            User::findOrFail($validated['user_id']),
            Permission::findOrFail($validated['permission_id']),
            $validated['scope_id'] ? Scope::find($validated['scope_id']) : null
        );

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Permission assigned successfully' : 'Permission already assigned',
        ]);
    }

    /**
     * Use as job
     */
    public function asJob(User $user, Permission $permission, ?Scope $scope = null)
    {
        $this->handle($user, $permission, $scope);
    }
}
