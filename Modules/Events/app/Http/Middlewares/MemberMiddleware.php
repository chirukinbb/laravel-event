<?php

namespace Modules\Events\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;

class MemberMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $member = $request->route('member');
        $event = $request->route('event');
        if (!$event->members->contains($member)) {
            return response()->json([
                'message' => 'You are not a member of this event'
            ], 403);
        }
        return $next($request);
    }
}