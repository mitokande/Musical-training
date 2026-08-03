<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stateless twin of CheckUserRestriction: suspended or restricted accounts get
 * a JSON 403 instead of a redirect to a web page.
 */
class EnsureApiUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isSuspended()) {
            return response()->json([
                'error' => [
                    'code' => 'account_suspended',
                    'message' => __('Your account has been suspended.'),
                ],
            ], 403);
        }

        if ($user && $user->isRestricted()) {
            return response()->json([
                'error' => [
                    'code' => 'account_restricted',
                    'message' => __('Your account is currently restricted.'),
                ],
            ], 403);
        }

        return $next($request);
    }
}
