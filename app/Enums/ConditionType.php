<?php

namespace App\Enums;

/**
 * Enum: ConditionType
 *
 * Purpose: Define condition types for contextual permissions
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
enum ConditionType: string
{
    // Time-based conditions
    case TIME_RANGE = 'time_range';
    case DAYS = 'days';
    case DATE_RANGE = 'date_range';

    // Network conditions
    case IP_WHITELIST = 'ip_whitelist';
    case IP_BLACKLIST = 'ip_blacklist';

    // Security conditions
    case REQUIRES_2FA = 'requires_2fa';
    case REQUIRES_EMAIL_VERIFIED = 'requires_email_verified';

    // Business conditions
    case MAX_AMOUNT = 'max_amount';
    case MIN_AMOUNT = 'min_amount';

    // User attribute conditions
    case USER_ATTRIBUTES = 'user_attributes';

    // Custom conditions
    case CUSTOM = 'custom';

    /**
     * Get all time-based condition types
     *
     * @return array<string>
     */
    public static function timeConditions(): array
    {
        return [
            self::TIME_RANGE->value,
            self::DAYS->value,
            self::DATE_RANGE->value,
        ];
    }

    /**
     * Get all network-based condition types
     *
     * @return array<string>
     */
    public static function networkConditions(): array
    {
        return [
            self::IP_WHITELIST->value,
            self::IP_BLACKLIST->value,
        ];
    }

    /**
     * Get all security condition types
     *
     * @return array<string>
     */
    public static function securityConditions(): array
    {
        return [
            self::REQUIRES_2FA->value,
            self::REQUIRES_EMAIL_VERIFIED->value,
        ];
    }

    /**
     * Get all business condition types
     *
     * @return array<string>
     */
    public static function businessConditions(): array
    {
        return [
            self::MAX_AMOUNT->value,
            self::MIN_AMOUNT->value,
        ];
    }

    /**
     * Get condition type description
     */
    public function description(): string
    {
        return match ($this) {
            self::TIME_RANGE => 'Allow only during specific hours (e.g., 9:00-18:00)',
            self::DAYS => 'Allow only on specific days of week',
            self::DATE_RANGE => 'Allow only between specific dates',

            self::IP_WHITELIST => 'Allow only from specific IP addresses',
            self::IP_BLACKLIST => 'Block specific IP addresses',

            self::REQUIRES_2FA => 'Require two-factor authentication',
            self::REQUIRES_EMAIL_VERIFIED => 'Require verified email address',

            self::MAX_AMOUNT => 'Limit maximum transaction amount',
            self::MIN_AMOUNT => 'Require minimum transaction amount',

            self::USER_ATTRIBUTES => 'Require specific user attributes (e.g., subscription: premium)',

            self::CUSTOM => 'Custom condition logic',
        };
    }

    /**
     * Get example value for condition type
     */
    public function exampleValue(): mixed
    {
        return match ($this) {
            self::TIME_RANGE => ['start' => '09:00', 'end' => '18:00'],
            self::DAYS => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            self::DATE_RANGE => ['start' => '2025-01-01', 'end' => '2025-12-31'],

            self::IP_WHITELIST => ['192.168.1.0/24', '10.0.0.1'],
            self::IP_BLACKLIST => ['203.0.113.0/24'],

            self::REQUIRES_2FA => true,
            self::REQUIRES_EMAIL_VERIFIED => true,

            self::MAX_AMOUNT => 5000,
            self::MIN_AMOUNT => 100,

            self::USER_ATTRIBUTES => ['subscription' => 'premium', 'account_age_days' => 90],

            self::CUSTOM => ['custom_rule' => 'value'],
        };
    }

    /**
     * Get Heroicon for condition type
     */
    public function icon(): string
    {
        return match ($this) {
            self::TIME_RANGE, self::DAYS, self::DATE_RANGE => 'heroicon-o-clock',
            self::IP_WHITELIST, self::IP_BLACKLIST => 'heroicon-o-globe-alt',
            self::REQUIRES_2FA => 'heroicon-o-shield-check',
            self::REQUIRES_EMAIL_VERIFIED => 'heroicon-o-envelope-open',
            self::MAX_AMOUNT, self::MIN_AMOUNT => 'heroicon-o-currency-dollar',
            self::USER_ATTRIBUTES => 'heroicon-o-user-circle',
            self::CUSTOM => 'heroicon-o-code-bracket',
        };
    }

    /**
     * Get color for Filament badge
     */
    public function color(): string
    {
        return match ($this) {
            self::TIME_RANGE, self::DAYS, self::DATE_RANGE => 'info',
            self::IP_WHITELIST => 'success',
            self::IP_BLACKLIST => 'danger',
            self::REQUIRES_2FA, self::REQUIRES_EMAIL_VERIFIED => 'warning',
            self::MAX_AMOUNT, self::MIN_AMOUNT => 'primary',
            self::USER_ATTRIBUTES => 'gray',
            self::CUSTOM => 'purple',
        };
    }

    /**
     * Validate condition value structure
     */
    public function validateValue(mixed $value): bool
    {
        return match ($this) {
            self::TIME_RANGE => is_array($value) && isset($value['start']) && isset($value['end']),
            self::DAYS => is_array($value) && ! empty($value),
            self::DATE_RANGE => is_array($value) && isset($value['start']) && isset($value['end']),

            self::IP_WHITELIST, self::IP_BLACKLIST => is_array($value) && ! empty($value),

            self::REQUIRES_2FA, self::REQUIRES_EMAIL_VERIFIED => is_bool($value),

            self::MAX_AMOUNT, self::MIN_AMOUNT => is_numeric($value) && $value > 0,

            self::USER_ATTRIBUTES => is_array($value) && ! empty($value),

            self::CUSTOM => true, // Always valid for custom
        };
    }
}
