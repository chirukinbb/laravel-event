<?php

namespace Modules\Events\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Modules\Events\Models\Event;

class OrganizerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $userEventsCount = Event::where('user_id', $request->user()->id)->count();

        if ($userEventsCount >= 3) {
            return response()->json([
                'message' => 'You are not authorized to create more than 3 events.',
            ], 422);
        }

        return $next($request);
    }
}