<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRestriction
{
    private array $allowedPaths = [
        '/',
        '/dashboard',
        '/logout',
        '/language/switch',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->isRestricted() && ! $user->isAdmin()) {
            $path = $request->getPathInfo();

            $isAllowed = collect($this->allowedPaths)->contains(
                fn ($allowed) => $path === $allowed || str_starts_with($path, $allowed.'/')
            );

            if (! $isAllowed) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Hesabınız kısıtlanmış durumda. Bu sayfaya erişim izniniz yok.');
            }
        }

        return $next($request);
    }
}
