<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Models\Permission;
use App\Models\PermissionTemplate;
use App\Models\PermissionAuditLog;
use App\Models\PermissionDelegation;
use App\Enums\AuditAction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * PermissionAnalytics Service
 *
 * Analytics and statistics for permission system
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionAnalytics
{
    /**
     * Get permission usage statistics
     *
     * @param Carbon|null $startDate Start date for analysis
     * @param Carbon|null $endDate End date for analysis
     * @return array
     */
    public function getPermissionUsageStats(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $cacheKey = "analytics:permission_usage:{$startDate->format('Y-m-d')}:{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, 3600, function () use ($startDate, $endDate) {
            // Most granted permissions
            $mostGranted = PermissionAuditLog::where('action', AuditAction::GRANTED->value)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('permission_slug', DB::raw('count(*) as grant_count'))
                ->groupBy('permission_slug')
                ->orderByDesc('grant_count')
                ->limit(10)
                ->get();

            // Most revoked permissions
            $mostRevoked = PermissionAuditLog::where('action', AuditAction::REVOKED->value)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('permission_slug', DB::raw('count(*) as revoke_count'))
                ->groupBy('permission_slug')
                ->orderByDesc('revoke_count')
                ->limit(10)
                ->get();

            // Total activity
            $totalGrants = PermissionAuditLog::where('action', AuditAction::GRANTED->value)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $totalRevokes = PermissionAuditLog::where('action', AuditAction::REVOKED->value)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            return [
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'most_granted' => $mostGranted,
                'most_revoked' => $mostRevoked,
                'total_grants' => $totalGrants,
                'total_revokes' => $totalRevokes,
                'net_change' => $totalGrants - $totalRevokes,
            ];
        });
    }

    /**
     * Get user permission summary
     *
     * @param User $user User to analyze
     * @return array
     */
    public function getUserPermissionSummary(User $user): array
    {
        $cacheKey = "analytics:user_permissions:{$user->id}";

        return Cache::remember($cacheKey, 600, function () use ($user) {
            // Direct permissions count
            $directPermissions = $user->permissions()->count();

            // Template permissions count
            $templatePermissions = 0;
            foreach ($user->templates as $template) {
                $templatePermissions += $template->getAllPermissions()->count();
            }

            // Active delegations
            $activeDelegations = PermissionDelegation::active()
                ->where('delegatee_id', $user->id)
                ->count();

            // Recent activity
            $recentGrants = PermissionAuditLog::where('user_id', $user->id)
                ->where('action', AuditAction::GRANTED->value)
                ->where('created_at', '>', now()->subDays(7))
                ->count();

            $recentRevokes = PermissionAuditLog::where('user_id', $user->id)
                ->where('action', AuditAction::REVOKED->value)
                ->where('created_at', '>', now()->subDays(7))
                ->count();

            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'direct_permissions' => $directPermissions,
                'template_permissions' => $templatePermissions,
                'active_delegations' => $activeDelegations,
                'total_permissions' => $directPermissions + $templatePermissions + $activeDelegations,
                'recent_activity' => [
                    'grants_last_7_days' => $recentGrants,
                    'revokes_last_7_days' => $recentRevokes,
                ],
            ];
        });
    }

    /**
     * Get template usage statistics
     *
     * @return array
     */
    public function getTemplateUsageStats(): array
    {
        $cacheKey = "analytics:template_usage";

        return Cache::remember($cacheKey, 3600, function () {
            // Most used templates
            $mostUsed = DB::table('user_templates')
                ->select('template_id', DB::raw('count(*) as user_count'))
                ->groupBy('template_id')
                ->orderByDesc('user_count')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $template = PermissionTemplate::find($item->template_id);
                    return [
                        'template_id' => $item->template_id,
                        'template_name' => $template?->name,
                        'user_count' => $item->user_count,
                    ];
                });

            // Templates by permission count
            $templates = PermissionTemplate::withCount('permissions')
                ->orderByDesc('permissions_count')
                ->limit(10)
                ->get()
                ->map(function ($template) {
                    return [
                        'template_id' => $template->id,
                        'template_name' => $template->name,
                        'permissions_count' => $template->permissions_count,
                    ];
                });

            return [
                'most_used_templates' => $mostUsed,
                'templates_by_permission_count' => $templates,
                'total_templates' => PermissionTemplate::count(),
            ];
        });
    }

    /**
     * Get delegation statistics
     *
     * @return array
     */
    public function getDelegationStats(): array
    {
        $cacheKey = "analytics:delegation_stats";

        return Cache::remember($cacheKey, 1800, function () {
            $activeDelegations = PermissionDelegation::active()->count();
            $expiredDelegations = PermissionDelegation::expired()->count();
            $revokedDelegations = PermissionDelegation::revoked()->count();

            // Top delegators
            $topDelegators = PermissionDelegation::select('delegator_id', 'delegator_name', DB::raw('count(*) as delegation_count'))
                ->groupBy('delegator_id', 'delegator_name')
                ->orderByDesc('delegation_count')
                ->limit(10)
                ->get();

            // Delegations expiring soon (next 7 days)
            $expiringSoon = PermissionDelegation::active()
                ->whereBetween('valid_until', [now(), now()->addDays(7)])
                ->count();

            return [
                'active_delegations' => $activeDelegations,
                'expired_delegations' => $expiredDelegations,
                'revoked_delegations' => $revokedDelegations,
                'expiring_soon' => $expiringSoon,
                'top_delegators' => $topDelegators,
            ];
        });
    }

    /**
     * Get audit activity timeline
     *
     * @param int $days Number of days to analyze
     * @return array
     */
    public function getAuditTimeline(int $days = 30): array
    {
        $cacheKey = "analytics:audit_timeline:{$days}";

        return Cache::remember($cacheKey, 1800, function () use ($days) {
            $startDate = now()->subDays($days);

            $timeline = PermissionAuditLog::select(
                DB::raw('DATE(created_at) as date'),
                'action',
                DB::raw('count(*) as count')
            )
                ->where('created_at', '>', $startDate)
                ->groupBy('date', 'action')
                ->orderBy('date')
                ->get()
                ->groupBy('date')
                ->map(function ($dayLogs, $date) {
                    $actions = $dayLogs->pluck('count', 'action')->toArray();
                    return [
                        'date' => $date,
                        'granted' => $actions[AuditAction::GRANTED->value] ?? 0,
                        'revoked' => $actions[AuditAction::REVOKED->value] ?? 0,
                        'delegated' => $actions[AuditAction::DELEGATED->value] ?? 0,
                        'total' => array_sum($actions),
                    ];
                });

            return [
                'period_days' => $days,
                'timeline' => $timeline->values()->toArray(),
            ];
        });
    }

    /**
     * Get system health metrics
     *
     * @return array
     */
    public function getSystemHealthMetrics(): array
    {
        $cacheKey = "analytics:system_health";

        return Cache::remember($cacheKey, 600, function () {
            // Users with excessive permissions (>100)
            $usersWithExcessivePermissions = User::has('permissions', '>', 100)->count();

            // Unused permissions
            $totalPermissions = Permission::count();
            $usedPermissions = DB::table('user_permissions')
                ->distinct('permission_id')
                ->count('permission_id');
            $unusedPermissions = $totalPermissions - $usedPermissions;

            // Templates without users
            $templatesWithoutUsers = PermissionTemplate::doesntHave('users')->count();

            // Delegations needing review (expiring in 3 days)
            $delegationsNeedingReview = PermissionDelegation::active()
                ->whereBetween('valid_until', [now(), now()->addDays(3)])
                ->count();

            return [
                'users_with_excessive_permissions' => $usersWithExcessivePermissions,
                'unused_permissions' => $unusedPermissions,
                'unused_permissions_percentage' => $totalPermissions > 0 ? round(($unusedPermissions / $totalPermissions) * 100, 2) : 0,
                'templates_without_users' => $templatesWithoutUsers,
                'delegations_needing_review' => $delegationsNeedingReview,
            ];
        });
    }

    /**
     * Invalidate all analytics caches
     *
     * @return void
     */
    public function invalidateAllCaches(): void
    {
        Cache::tags(['analytics'])->flush();
    }
}
