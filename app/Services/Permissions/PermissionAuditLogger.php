<?php

namespace App\Services\Permissions;

use App\Models\User;
use App\Models\Permission;
use App\Models\Scope;
use App\Models\PermissionTemplate;
use App\Models\PermissionDelegation;
use App\Models\PermissionRequest;
use App\Models\PermissionAuditLog;
use App\Enums\AuditAction;
use Illuminate\Support\Facades\Log;

/**
 * PermissionAuditLogger Service
 *
 * Comprehensive audit logging for all permission changes
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionAuditLogger
{
    /**
     * Log permission grant
     *
     * @param User $user User receiving permission
     * @param Permission $permission Permission granted
     * @param Scope|null $scope Scope context
     * @param User $performedBy User granting permission
     * @param string|null $reason Reason for grant
     * @param string $source Source (direct, template, wildcard, inherited)
     * @param int|null $sourceId Source ID (template_id, wildcard_id, etc.)
     * @return PermissionAuditLog
     */
    public function logGrant(
        User $user,
        Permission $permission,
        ?Scope $scope,
        User $performedBy,
        ?string $reason = null,
        string $source = 'direct',
        ?int $sourceId = null
    ): PermissionAuditLog {
        return $this->createLog([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::GRANTED->value,
            'permission_slug' => $permission->slug,
            'permission_name' => $permission->name,
            'source' => $source,
            'source_id' => $sourceId,
            'source_name' => $this->getSourceName($source, $sourceId),
            'scope_id' => $scope?->id,
            'performed_by' => $performedBy->id,
            'performed_by_name' => $performedBy->name,
            'reason' => $reason,
            'metadata' => [
                'permission_group' => $permission->group?->name,
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log permission revoke
     *
     * @param User $user User losing permission
     * @param Permission $permission Permission revoked
     * @param User $performedBy User revoking permission
     * @param string|null $reason Reason for revoke
     * @return PermissionAuditLog
     */
    public function logRevoke(
        User $user,
        Permission $permission,
        User $performedBy,
        ?string $reason = null
    ): PermissionAuditLog {
        return $this->createLog([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::REVOKED->value,
            'permission_slug' => $permission->slug,
            'permission_name' => $permission->name,
            'source' => 'direct',
            'performed_by' => $performedBy->id,
            'performed_by_name' => $performedBy->name,
            'reason' => $reason,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log template assignment
     *
     * @param User $user User receiving template
     * @param PermissionTemplate $template Template assigned
     * @param User $performedBy User assigning template
     * @param Scope|null $scope Scope context
     * @param string|null $reason Reason for assignment
     * @return PermissionAuditLog
     */
    public function logTemplateAssignment(
        User $user,
        PermissionTemplate $template,
        User $performedBy,
        ?Scope $scope = null,
        ?string $reason = null
    ): PermissionAuditLog {
        return $this->createLog([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::TEMPLATE_ASSIGNED->value,
            'permission_slug' => 'template.' . $template->slug,
            'permission_name' => $template->name,
            'source' => 'template',
            'source_id' => $template->id,
            'source_name' => $template->name,
            'scope_id' => $scope?->id,
            'performed_by' => $performedBy->id,
            'performed_by_name' => $performedBy->name,
            'reason' => $reason,
            'metadata' => [
                'template_permissions_count' => $template->permissions->count(),
                'template_wildcards_count' => $template->wildcards->count(),
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log template removal
     *
     * @param User $user User losing template
     * @param PermissionTemplate $template Template removed
     * @param User $performedBy User removing template
     * @param string|null $reason Reason for removal
     * @return PermissionAuditLog
     */
    public function logTemplateRemoval(
        User $user,
        PermissionTemplate $template,
        User $performedBy,
        ?string $reason = null
    ): PermissionAuditLog {
        return $this->createLog([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => AuditAction::TEMPLATE_REMOVED->value,
            'permission_slug' => 'template.' . $template->slug,
            'permission_name' => $template->name,
            'source' => 'template',
            'source_id' => $template->id,
            'source_name' => $template->name,
            'performed_by' => $performedBy->id,
            'performed_by_name' => $performedBy->name,
            'reason' => $reason,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log delegation creation
     *
     * @param PermissionDelegation $delegation Delegation created
     * @return PermissionAuditLog
     */
    public function logDelegation(PermissionDelegation $delegation): PermissionAuditLog
    {
        return $this->createLog([
            'user_id' => $delegation->delegatee_id,
            'user_name' => $delegation->delegatee_name,
            'user_email' => null,
            'action' => AuditAction::DELEGATED->value,
            'permission_slug' => $delegation->permission_slug,
            'permission_name' => null,
            'source' => 'delegation',
            'source_id' => $delegation->id,
            'source_name' => 'Delegation from ' . $delegation->delegator_name,
            'scope_id' => $delegation->scope_id,
            'performed_by' => $delegation->delegator_id,
            'performed_by_name' => $delegation->delegator_name,
            'reason' => $delegation->reason,
            'metadata' => [
                'valid_until' => $delegation->valid_until->toDateTimeString(),
                'can_redelegate' => $delegation->can_redelegate,
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Log permission request
     *
     * @param PermissionRequest $request Request created/updated
     * @param string $action Action (requested, approved, rejected)
     * @return PermissionAuditLog
     */
    public function logRequest(PermissionRequest $request, string $action): PermissionAuditLog
    {
        $auditAction = match($action) {
            'requested' => AuditAction::REQUESTED,
            'approved' => AuditAction::REQUEST_APPROVED,
            'rejected' => AuditAction::REQUEST_REJECTED,
            default => AuditAction::UPDATED,
        };

        return $this->createLog([
            'user_id' => $request->user_id,
            'user_name' => $request->user->name,
            'user_email' => $request->user->email,
            'action' => $auditAction->value,
            'permission_slug' => $request->permission->slug,
            'permission_name' => $request->permission->name,
            'source' => 'request',
            'source_id' => $request->id,
            'scope_id' => $request->scope_id,
            'performed_by' => $request->reviewed_by,
            'performed_by_name' => $request->reviewer?->name,
            'reason' => $request->reason,
            'metadata' => [
                'status' => $request->status,
                'review_comment' => $request->review_comment,
            ],
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }

    /**
     * Create audit log entry
     *
     * @param array $data
     * @return PermissionAuditLog
     */
    private function createLog(array $data): PermissionAuditLog
    {
        $log = PermissionAuditLog::create($data);

        Log::info('Permission audit log created', [
            'log_id' => $log->id,
            'action' => $data['action'],
            'user_id' => $data['user_id'],
            'permission_slug' => $data['permission_slug'],
        ]);

        return $log;
    }

    /**
     * Get source name from source type and ID
     *
     * @param string $source
     * @param int|null $sourceId
     * @return string|null
     */
    private function getSourceName(string $source, ?int $sourceId): ?string
    {
        if (!$sourceId) {
            return null;
        }

        return match($source) {
            'template' => PermissionTemplate::find($sourceId)?->name,
            'wildcard' => 'Wildcard #' . $sourceId,
            default => null,
        };
    }
}
