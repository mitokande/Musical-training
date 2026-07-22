<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (! $user->canAccess($feature)) {
            // Feature-specific upgrade copy; falls back to the generic line.
            $key = 'app.limits.feature_'.$feature;
            $message = __($key) !== $key ? __($key) : __('app.limits.feature_premium_generic');

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'feature_restricted',
                    'message' => $message,
                    'feature' => $feature,
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', $message);
        }

        return $next($request);
    }
}
