<?php

namespace App\Enums;

/**
 * Enum: AuditAction
 *
 * Purpose: Define auditable actions for permission_audit_log table
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
enum AuditAction: string
{
    // Permission lifecycle
    case GRANTED = 'granted';
    case REVOKED = 'revoked';
    case EXPIRED = 'expired';
    case UPDATED = 'updated';

    // Inheritance sources
    case INHERITED = 'inherited';
    case INHERITED_REMOVED = 'inherited_removed';

    // Template operations
    case TEMPLATE_ASSIGNED = 'template_assigned';
    case TEMPLATE_REMOVED = 'template_removed';
    case TEMPLATE_SYNCED = 'template_synced';

    // Delegation operations
    case DELEGATED = 'delegated';
    case DELEGATION_REVOKED = 'delegation_revoked';
    case DELEGATION_EXPIRED = 'delegation_expired';

    // Request workflow
    case REQUESTED = 'requested';
    case REQUEST_APPROVED = 'request_approved';
    case REQUEST_REJECTED = 'request_rejected';

    /**
     * Get all permission lifecycle actions
     *
     * @return array<string>
     */
    public static function lifecycleActions(): array
    {
        return [
            self::GRANTED->value,
            self::REVOKED->value,
            self::EXPIRED->value,
            self::UPDATED->value,
        ];
    }

    /**
     * Get all template-related actions
     *
     * @return array<string>
     */
    public static function templateActions(): array
    {
        return [
            self::TEMPLATE_ASSIGNED->value,
            self::TEMPLATE_REMOVED->value,
            self::TEMPLATE_SYNCED->value,
        ];
    }

    /**
     * Get all delegation-related actions
     *
     * @return array<string>
     */
    public static function delegationActions(): array
    {
        return [
            self::DELEGATED->value,
            self::DELEGATION_REVOKED->value,
            self::DELEGATION_EXPIRED->value,
        ];
    }

    /**
     * Get all request workflow actions
     *
     * @return array<string>
     */
    public static function requestActions(): array
    {
        return [
            self::REQUESTED->value,
            self::REQUEST_APPROVED->value,
            self::REQUEST_REJECTED->value,
        ];
    }

    /**
     * Get action description
     */
    public function description(): string
    {
        return match ($this) {
            self::GRANTED => 'Permission granted to user',
            self::REVOKED => 'Permission revoked from user',
            self::EXPIRED => 'Permission expired automatically',
            self::UPDATED => 'Permission updated',

            self::INHERITED => 'Permission inherited from parent',
            self::INHERITED_REMOVED => 'Inherited permission removed',

            self::TEMPLATE_ASSIGNED => 'Template assigned to user',
            self::TEMPLATE_REMOVED => 'Template removed from user',
            self::TEMPLATE_SYNCED => 'Permissions synced from template',

            self::DELEGATED => 'Permission delegated to user',
            self::DELEGATION_REVOKED => 'Delegation revoked',
            self::DELEGATION_EXPIRED => 'Delegation expired',

            self::REQUESTED => 'Permission requested by user',
            self::REQUEST_APPROVED => 'Permission request approved',
            self::REQUEST_REJECTED => 'Permission request rejected',
        };
    }

    /**
     * Get Heroicon for action
     */
    public function icon(): string
    {
        return match ($this) {
            self::GRANTED => 'heroicon-o-check-circle',
            self::REVOKED => 'heroicon-o-x-circle',
            self::EXPIRED => 'heroicon-o-clock',
            self::UPDATED => 'heroicon-o-pencil',

            self::INHERITED => 'heroicon-o-arrow-down',
            self::INHERITED_REMOVED => 'heroicon-o-arrow-up',

            self::TEMPLATE_ASSIGNED => 'heroicon-o-clipboard-document-check',
            self::TEMPLATE_REMOVED => 'heroicon-o-clipboard-document-list',
            self::TEMPLATE_SYNCED => 'heroicon-o-arrow-path',

            self::DELEGATED => 'heroicon-o-user-plus',
            self::DELEGATION_REVOKED => 'heroicon-o-user-minus',
            self::DELEGATION_EXPIRED => 'heroicon-o-clock',

            self::REQUESTED => 'heroicon-o-hand-raised',
            self::REQUEST_APPROVED => 'heroicon-o-check-badge',
            self::REQUEST_REJECTED => 'heroicon-o-x-circle',
        };
    }

    /**
     * Get color for Filament badge
     */
    public function color(): string
    {
        return match ($this) {
            self::GRANTED, self::TEMPLATE_ASSIGNED, self::REQUEST_APPROVED => 'success',
            self::REVOKED, self::TEMPLATE_REMOVED, self::DELEGATION_REVOKED, self::REQUEST_REJECTED => 'danger',
            self::EXPIRED, self::DELEGATION_EXPIRED => 'warning',
            self::UPDATED, self::TEMPLATE_SYNCED => 'info',
            self::INHERITED, self::INHERITED_REMOVED => 'gray',
            self::DELEGATED => 'primary',
            self::REQUESTED => 'warning',
        };
    }

    /**
     * Check if action is positive (grant/approve)
     */
    public function isPositive(): bool
    {
        return in_array($this, [
            self::GRANTED,
            self::TEMPLATE_ASSIGNED,
            self::DELEGATED,
            self::REQUEST_APPROVED,
        ]);
    }

    /**
     * Check if action is negative (revoke/reject)
     */
    public function isNegative(): bool
    {
        return in_array($this, [
            self::REVOKED,
            self::TEMPLATE_REMOVED,
            self::DELEGATION_REVOKED,
            self::REQUEST_REJECTED,
        ]);
    }
}
