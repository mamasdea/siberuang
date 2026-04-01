<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuPermission;

class MenuAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $menuKey)
    {
        if (Auth::check() && MenuPermission::hasAccess(Auth::user()->role, $menuKey)) {
            return $next($request);
        }

        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
