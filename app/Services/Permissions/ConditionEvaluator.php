<?php

namespace App\Services\Permissions;

use App\Enums\ConditionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * ConditionEvaluator Service
 *
 * Evaluates contextual conditions for permission grants
 *
 * @author Noflaye Box Team
 *
 * @version 1.0.0
 */
class ConditionEvaluator
{
    /**
     * Evaluate all conditions
     *
     * @param  array  $conditions  Array of conditions to evaluate
     * @param  User  $user  The user context
     * @param  Request|null  $request  The request context
     * @return bool True if all conditions pass
     */
    public function evaluate(array $conditions, User $user, ?Request $request = null): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $type => $value) {
            try {
                $conditionType = ConditionType::from($type);

                if (! $this->evaluateCondition($conditionType, $value, $user, $request)) {
                    return false;
                }
            } catch (\ValueError $e) {
                // Unknown condition type, fail safe
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate single condition
     */
    private function evaluateCondition(
        ConditionType $type,
        mixed $value,
        User $user,
        ?Request $request
    ): bool {
        return match ($type) {
            ConditionType::TIME_RANGE => $this->evaluateTimeRange($value),
            ConditionType::DAYS => $this->evaluateDays($value),
            ConditionType::DATE_RANGE => $this->evaluateDateRange($value),
            ConditionType::IP_WHITELIST => $this->evaluateIpWhitelist($value, $request),
            ConditionType::IP_BLACKLIST => $this->evaluateIpBlacklist($value, $request),
            ConditionType::REQUIRES_2FA => $this->evaluateRequires2FA($value, $user),
            ConditionType::REQUIRES_EMAIL_VERIFIED => $this->evaluateEmailVerified($value, $user),
            ConditionType::MAX_AMOUNT => $this->evaluateMaxAmount($value, $request),
            ConditionType::MIN_AMOUNT => $this->evaluateMinAmount($value, $request),
            ConditionType::USER_ATTRIBUTES => $this->evaluateUserAttributes($value, $user),
            ConditionType::CUSTOM => $this->evaluateCustom($value, $user, $request),
        };
    }

    /**
     * Evaluate time range condition
     *
     * @param  array  $value  ['start' => '09:00', 'end' => '18:00']
     */
    private function evaluateTimeRange(array $value): bool
    {
        $now = now();
        $start = Carbon::createFromTimeString($value['start']);
        $end = Carbon::createFromTimeString($value['end']);

        return $now->between($start, $end);
    }

    /**
     * Evaluate days condition
     *
     * @param  array  $value  ['monday', 'tuesday', ...]
     */
    private function evaluateDays(array $value): bool
    {
        $today = strtolower(now()->englishDayOfWeek);

        return in_array($today, array_map('strtolower', $value));
    }

    /**
     * Evaluate date range condition
     *
     * @param  array  $value  ['start' => '2025-01-01', 'end' => '2025-12-31']
     */
    private function evaluateDateRange(array $value): bool
    {
        $now = now();
        $start = Carbon::parse($value['start']);
        $end = Carbon::parse($value['end']);

        return $now->between($start, $end);
    }

    /**
     * Evaluate IP whitelist condition
     *
     * @param  array  $value  ['192.168.1.0/24', '10.0.0.1']
     */
    private function evaluateIpWhitelist(array $value, ?Request $request): bool
    {
        if (! $request) {
            return false;
        }

        $clientIp = $request->ip();

        foreach ($value as $allowedIp) {
            if ($this->ipMatches($clientIp, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate IP blacklist condition
     *
     * @param  array  $value  ['203.0.113.0/24']
     */
    private function evaluateIpBlacklist(array $value, ?Request $request): bool
    {
        if (! $request) {
            return true; // No request, can't be blacklisted
        }

        $clientIp = $request->ip();

        foreach ($value as $blockedIp) {
            if ($this->ipMatches($clientIp, $blockedIp)) {
                return false; // IP is blacklisted
            }
        }

        return true;
    }

    /**
     * Evaluate 2FA requirement
     */
    private function evaluateRequires2FA(bool $value, User $user): bool
    {
        if (! $value) {
            return true;
        }

        return $user->two_factor_confirmed_at !== null;
    }

    /**
     * Evaluate email verification requirement
     */
    private function evaluateEmailVerified(bool $value, User $user): bool
    {
        if (! $value) {
            return true;
        }

        return $user->hasVerifiedEmail();
    }

    /**
     * Evaluate max amount condition
     */
    private function evaluateMaxAmount(float $value, ?Request $request): bool
    {
        if (! $request) {
            return true;
        }

        $amount = $request->input('amount', 0);

        return $amount <= $value;
    }

    /**
     * Evaluate min amount condition
     */
    private function evaluateMinAmount(float $value, ?Request $request): bool
    {
        if (! $request) {
            return true;
        }

        $amount = $request->input('amount', 0);

        return $amount >= $value;
    }

    /**
     * Evaluate user attributes condition
     *
     * @param  array  $value  ['subscription' => 'premium', 'account_age_days' => 90]
     */
    private function evaluateUserAttributes(array $value, User $user): bool
    {
        foreach ($value as $attribute => $expectedValue) {
            $actualValue = data_get($user, $attribute);

            if ($actualValue !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate custom condition
     */
    private function evaluateCustom(array $value, User $user, ?Request $request): bool
    {
        // Implement custom logic here
        // Could call external service, check database, etc.
        return true;
    }

    /**
     * Check if IP matches pattern (supports CIDR)
     */
    private function ipMatches(string $ip, string $pattern): bool
    {
        // Simple IP match
        if ($ip === $pattern) {
            return true;
        }

        // CIDR notation
        if (str_contains($pattern, '/')) {
            [$subnet, $mask] = explode('/', $pattern);

            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = -1 << (32 - (int) $mask);

            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        return false;
    }
}
