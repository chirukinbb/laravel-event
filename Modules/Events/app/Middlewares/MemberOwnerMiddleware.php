<?php

namespace Modules\Events\Middlewares;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class MemberOwnerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $member = $request->route('member');
        $event = $request->route('event');


        if (!$event->members->contains($member)) {
            return response()->json([
                'message' => 'You are not allowed to do this action'
            ], 403);
        }

        if ($member->user_id !== $request->user()->id()) {
            return response()->json([
                'message' => 'You are not allowed to do this action'
            ], 403);
        }

        if (Carbon::parse($event->planing_date)->addDay()->isPast()) {
            return response()->json([
                'message' => 'You are not allowed to do this action'
            ], 403);
        }

        return $next($request);
    }
}