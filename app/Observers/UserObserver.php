<?php

namespace App\Observers;

use App\Models\PermissionTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * UserObserver
 *
 * Handle User lifecycle events
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class UserObserver
{
    /**
     * Handle the User "created" event.
     * Auto-assign default template if no primary template is set
     */
    public function created(User $user): void
    {
        // Auto-assign default template if not set
        if (! $user->primary_template_id) {
            $defaultTemplate = PermissionTemplate::where('slug', 'default')
                ->where('is_active', true)
                ->first();

            if ($defaultTemplate) {
                $user->update(['primary_template_id' => $defaultTemplate->id]);
                $user->templates()->attach($defaultTemplate->id, [
                    'auto_sync' => true,
                    'sort_order' => 0,
                ]);

                Log::info('Default template assigned to new user', [
                    'user_id' => $user->id,
                    'template_id' => $defaultTemplate->id,
                ]);
            }
        }

        // Clear user cache
        $this->clearUserCache($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Clear cache if primary template changed
        if ($user->isDirty('primary_template_id')) {
            $this->clearUserCache($user);

            Log::info('User primary template changed', [
                'user_id' => $user->id,
                'old_template_id' => $user->getOriginal('primary_template_id'),
                'new_template_id' => $user->primary_template_id,
            ]);
        }
    }

    /**
     * Handle the User "deleting" event.
     * Cleanup all related data before deletion
     */
    public function deleting(User $user): void
    {
        // Detach all templates
        $user->templates()->detach();

        // Detach all user groups
        $user->userGroups()->detach();

        // Delete all permission requests
        $user->permissionRequests()->delete();

        // Delete delegations given and received
        $user->delegationsGiven()->delete();
        $user->delegationsReceived()->delete();

        // Clear cache
        $this->clearUserCache($user);

        Log::info('User being deleted, relationships cleaned up', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->clearUserCache($user);

        Log::info('User restored', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $this->clearUserCache($user);

        Log::info('User force deleted', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Clear user-related cache
     */
    private function clearUserCache(User $user): void
    {
        Cache::tags(['users', "user.{$user->id}"])->flush();
    }
}
