<?php

namespace App\Enums;

enum RequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable label for the request status
     *
     * Returns a localized French label for display in UIs.
     * Used in admin panels, request lists, and status badges.
     *
     * @return string Localized status label
     *
     * @example
     * RequestStatus::PENDING->label(); // Returns "En attente"
     * RequestStatus::APPROVED->label(); // Returns "Approuvé"
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::APPROVED => 'Approuvé',
            self::REJECTED => 'Rejeté',
            self::CANCELLED => 'Annulé',
        };
    }

    /**
     * Get Filament color for status display
     *
     * Returns the Filament color scheme appropriate for this status.
     * Used in badges, table columns, and status indicators.
     *
     * @return string Filament color name ('warning', 'success', 'danger', 'gray')
     *
     * @example
     * RequestStatus::PENDING->color(); // Returns "warning"
     * RequestStatus::APPROVED->color(); // Returns "success"
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    /**
     * Get Heroicon for status display
     *
     * Returns an appropriate Heroicon name for visual representation.
     * Used in Filament badges, table columns, and notifications.
     *
     * @return string Heroicon name (e.g., 'heroicon-o-clock', 'heroicon-o-check-circle')
     *
     * @example
     * RequestStatus::PENDING->icon(); // Returns "heroicon-o-clock"
     * RequestStatus::APPROVED->icon(); // Returns "heroicon-o-check-circle"
     */
    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::APPROVED => 'heroicon-o-check-circle',
            self::REJECTED => 'heroicon-o-x-circle',
            self::CANCELLED => 'heroicon-o-ban',
        };
    }

    /**
     * Check if status is final (cannot be changed)
     *
     * Final statuses cannot transition to other states. Requests in
     * final states are considered closed and archived.
     *
     * @return bool True if status is final (APPROVED, REJECTED, or CANCELLED)
     *
     * @example
     * RequestStatus::PENDING->isFinal(); // false
     * RequestStatus::APPROVED->isFinal(); // true
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::APPROVED, self::REJECTED, self::CANCELLED]);
    }

    /**
     * Check if this status can transition to another status
     *
     * Validates state transitions based on business rules.
     * PENDING can transition to APPROVED, REJECTED, or CANCELLED.
     * Final statuses cannot transition to any other state.
     *
     * @param  self  $targetStatus  The desired target status
     * @return bool True if transition is allowed
     *
     * @example
     * RequestStatus::PENDING->canTransitionTo(RequestStatus::APPROVED); // true
     * RequestStatus::APPROVED->canTransitionTo(RequestStatus::REJECTED); // false
     * RequestStatus::PENDING->canTransitionTo(RequestStatus::CANCELLED); // true
     */
    public function canTransitionTo(self $targetStatus): bool
    {
        // Final statuses cannot transition
        if ($this->isFinal()) {
            return false;
        }

        // PENDING can transition to any final status
        if ($this === self::PENDING) {
            return in_array($targetStatus, [self::APPROVED, self::REJECTED, self::CANCELLED]);
        }

        return false;
    }

    /**
     * Check if status allows approval
     *
     * Only PENDING requests can be approved.
     *
     * @return bool True if request can be approved
     *
     * @example
     * RequestStatus::PENDING->canBeApproved(); // true
     * RequestStatus::APPROVED->canBeApproved(); // false
     */
    public function canBeApproved(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if status allows rejection
     *
     * Only PENDING requests can be rejected.
     *
     * @return bool True if request can be rejected
     *
     * @example
     * RequestStatus::PENDING->canBeRejected(); // true
     * RequestStatus::APPROVED->canBeRejected(); // false
     */
    public function canBeRejected(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if status allows cancellation
     *
     * Only PENDING requests can be cancelled.
     *
     * @return bool True if request can be cancelled
     *
     * @example
     * RequestStatus::PENDING->canBeCancelled(); // true
     * RequestStatus::APPROVED->canBeCancelled(); // false
     */
    public function canBeCancelled(): bool
    {
        return $this === self::PENDING;
    }
}
