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

        if (!$user) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if (!$user->canAccess($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'feature_restricted',
                    'message' => 'This feature requires a Premium plan.',
                    'feature' => $feature,
                ], 403);
            }

            return redirect()->route('dashboard')->with('error',
                'Bu özellik Premium plan gerektirir. Planınızı yükselterek erişim kazanın.'
            );
        }

        return $next($request);
    }
}
