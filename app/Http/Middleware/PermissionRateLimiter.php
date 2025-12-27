<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * PermissionRateLimiter Middleware
 *
 * Rate limit permission checks per user
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionRateLimiter
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @param string $permission Permission being checked
     * @param int $maxAttempts Maximum attempts (default: 60)
     * @param int $decayMinutes Decay time in minutes (default: 1)
     * @return Response
     */
    public function handle(
        Request $request,
        Closure $next,
        string $permission,
        int $maxAttempts = 60,
        int $decayMinutes = 1
    ): Response {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $key = $this->resolveRequestSignature($user->id, $permission, $request->ip());

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->logRateLimitExceeded($user, $permission, $request);

            return response()->json([
                'message' => 'Too many permission checks. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        return $next($request);
    }

    /**
     * Resolve request signature
     */
    private function resolveRequestSignature(int $userId, string $permission, string $ip): string
    {
        return "permission_check:{$userId}:{$permission}:{$ip}";
    }

    /**
     * Log rate limit exceeded
     */
    private function logRateLimitExceeded($user, string $permission, Request $request): void
    {
        Log::warning('Permission rate limit exceeded', [
            'user_id' => $user->id,
            'permission' => $permission,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Store in database for analytics
        DB::table('permission_rate_limits')->insert([
            'user_id' => $user->id,
            'permission' => $permission,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'exceeded_at' => now(),
        ]);
    }
}
