<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireStaff
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->isActiveStaff(), 403, 'Only approved staff can access management pages.');

        return $next($request);
    }
}
