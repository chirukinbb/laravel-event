<?php

namespace Modules\Events\Middlewares;

use Closure;
use Illuminate\Http\Request;

class HasFilterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()->filter) {
            return response([], 403);
        }
        return $next($request);
    }
}