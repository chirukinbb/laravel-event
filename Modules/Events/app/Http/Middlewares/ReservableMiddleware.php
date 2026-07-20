<?php

namespace Modules\Events\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;

class ReservableMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $event = $request->route('event');
        if ($event->slots <= $event->members->count()) {
            return response()->json([
                'message' => 'This event is not reservable',
            ], 400);
        }
        return $next($request);
    }
}