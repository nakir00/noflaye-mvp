<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Models\Permission;
use App\Models\Scope;
use App\Models\PermissionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * PermissionApprovalWorkflow Service
 *
 * Manage permission request/approval workflow
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionApprovalWorkflow
{
    public function __construct(
        private PermissionAuditLogger $auditLogger
    ) {}

    /**
     * Create permission request
     *
     * @param User $user User requesting permission
     * @param Permission $permission Permission requested
     * @param string $reason Reason for request
     * @param Scope|null $scope Scope context
     * @param array|null $metadata Additional metadata
     * @return PermissionRequest
     */
    public function createRequest(
        User $user,
        Permission $permission,
        string $reason,
        ?Scope $scope = null,
        ?array $metadata = null
    ): PermissionRequest {
        return DB::transaction(function () use ($user, $permission, $reason, $scope, $metadata) {
            $request = PermissionRequest::create([
                'user_id' => $user->id,
                'permission_id' => $permission->id,
                'scope_id' => $scope?->id,
                'reason' => $reason,
                'status' => 'pending',
                'requested_at' => now(),
                'metadata' => $metadata,
            ]);

            // Log request creation
            $this->auditLogger->logRequest($request, 'requested');

            Log::info('Permission request created', [
                'request_id' => $request->id,
                'user_id' => $user->id,
                'permission_slug' => $permission->slug,
            ]);

            return $request;
        });
    }

    /**
     * Approve permission request
     *
     * @param PermissionRequest $request Request to approve
     * @param User $reviewer User approving request
     * @param string|null $comment Review comment
     * @return bool
     */
    public function approveRequest(
        PermissionRequest $request,
        User $reviewer,
        ?string $comment = null
    ): bool {
        if ($request->status !== 'pending') {
            return false;
        }

        return DB::transaction(function () use ($request, $reviewer, $comment) {
            // Approve the request
            $result = $request->approve($reviewer, $comment);

            if ($result) {
                // Grant the actual permission
                $request->user->permissions()->attach($request->permission_id, [
                    'scope_id' => $request->scope_id,
                    'source' => 'request',
                    'granted_at' => now(),
                ]);

                // Log approval
                $this->auditLogger->logRequest($request, 'approved');

                Log::info('Permission request approved', [
                    'request_id' => $request->id,
                    'user_id' => $request->user_id,
                    'reviewer_id' => $reviewer->id,
                    'permission_slug' => $request->permission->slug,
                ]);
            }

            return $result;
        });
    }

    /**
     * Reject permission request
     *
     * @param PermissionRequest $request Request to reject
     * @param User $reviewer User rejecting request
     * @param string|null $comment Rejection reason
     * @return bool
     */
    public function rejectRequest(
        PermissionRequest $request,
        User $reviewer,
        ?string $comment = null
    ): bool {
        if ($request->status !== 'pending') {
            return false;
        }

        $result = $request->reject($reviewer, $comment);

        if ($result) {
            // Log rejection
            $this->auditLogger->logRequest($request, 'rejected');

            Log::info('Permission request rejected', [
                'request_id' => $request->id,
                'user_id' => $request->user_id,
                'reviewer_id' => $reviewer->id,
                'permission_slug' => $request->permission->slug,
            ]);
        }

        return $result;
    }

    /**
     * Get pending requests for review
     *
     * @param User|null $forReviewer Optional reviewer filter
     * @return Collection<PermissionRequest>
     */
    public function getPendingRequests(?User $forReviewer = null): Collection
    {
        $query = PermissionRequest::pending()
            ->with(['user', 'permission', 'scope'])
            ->orderBy('requested_at', 'asc');

        // Future: Add reviewer assignment logic
        // if ($forReviewer) {
        //     $query->where('assigned_to', $forReviewer->id);
        // }

        return $query->get();
    }

    /**
     * Get user's request history
     *
     * @param User $user User to check
     * @param string|null $status Optional status filter
     * @return Collection<PermissionRequest>
     */
    public function getUserRequestHistory(User $user, ?string $status = null): Collection
    {
        $query = PermissionRequest::where('user_id', $user->id)
            ->with(['permission', 'scope', 'reviewer'])
            ->orderBy('requested_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Bulk approve requests
     *
     * @param array $requestIds Request IDs to approve
     * @param User $reviewer User approving requests
     * @param string|null $comment Bulk comment
     * @return int Number of approved requests
     */
    public function bulkApproveRequests(array $requestIds, User $reviewer, ?string $comment = null): int
    {
        $approved = 0;

        DB::transaction(function () use ($requestIds, $reviewer, $comment, &$approved) {
            $requests = PermissionRequest::pending()
                ->whereIn('id', $requestIds)
                ->get();

            foreach ($requests as $request) {
                if ($this->approveRequest($request, $reviewer, $comment)) {
                    $approved++;
                }
            }
        });

        Log::info('Bulk approval completed', [
            'reviewer_id' => $reviewer->id,
            'total_requests' => count($requestIds),
            'approved_count' => $approved,
        ]);

        return $approved;
    }

    /**
     * Auto-expire old pending requests
     *
     * @param int $daysOld Number of days to consider old
     * @return int Number of expired requests
     */
    public function expireOldRequests(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);

        $expired = PermissionRequest::pending()
            ->where('requested_at', '<', $cutoffDate)
            ->get();

        foreach ($expired as $request) {
            $request->update([
                'status' => 'expired',
                'reviewed_at' => now(),
                'review_comment' => "Auto-expired after {$daysOld} days",
            ]);

            Log::info('Permission request auto-expired', [
                'request_id' => $request->id,
                'user_id' => $request->user_id,
                'days_old' => now()->diffInDays($request->requested_at),
            ]);
        }

        return $expired->count();
    }

    /**
     * Get request statistics
     *
     * @return array
     */
    public function getRequestStats(): array
    {
        return [
            'pending' => PermissionRequest::pending()->count(),
            'approved' => PermissionRequest::approved()->count(),
            'rejected' => PermissionRequest::rejected()->count(),
            'total' => PermissionRequest::count(),
            'avg_approval_time_hours' => PermissionRequest::approved()
                ->whereNotNull('reviewed_at')
                ->get()
                ->avg(function ($request) {
                    return $request->requested_at->diffInHours($request->reviewed_at);
                }),
        ];
    }
}
