<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Facades\Log;

/**
 * InvalidatePermissionCache Listener
 *
 * Invalidate permission cache when permissions change
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class InvalidatePermissionCache
{
    /**
     * Create the event listener
     */
    public function __construct(
        private PermissionChecker $permissionChecker
    ) {}

    /**
     * Handle the event
     *
     * @param object $event Event with user property
     */
    public function handle(object $event): void
    {
        if (!isset($event->user) || !$event->user instanceof User) {
            return;
        }

        // Invalidate user permission cache
        $this->permissionChecker->invalidateUserCache($event->user);

        Log::info('Permission cache invalidated', [
            'user_id' => $event->user->id,
            'event' => get_class($event),
        ]);
    }
}
