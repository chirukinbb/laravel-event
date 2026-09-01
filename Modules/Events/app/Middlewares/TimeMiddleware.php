<?php

namespace Modules\Events\Middlewares;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class TimeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $event = $request->route('event');

        if (Carbon::parse($event->planing_date)->addDay()->isPast()) {
            return response()->json([
                'message' => 'You are not allowed to do this action'
            ], 403);
        }

        return $next($request);
    }
}