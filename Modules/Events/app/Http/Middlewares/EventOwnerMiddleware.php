<?php

namespace Modules\Events\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;

class EventOwnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $event = $request->route('event');
        if ($event->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to access this event'], 403);
        }
        return $next($request);
    }
}