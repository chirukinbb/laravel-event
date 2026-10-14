<?php

namespace Modules\Events\Http\Middlewares;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class EventUpdateMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $event = $request->route('event');

        $hoursLimit = 5; // базовый интервал в часах

        $lastUpdated = Carbon::parse($event->updated_at);
        $nextAllowedTime = $lastUpdated->copy()->addHours($hoursLimit);

        if ($event->approved && $nextAllowedTime->isFuture()) {
            $diffInHours = now()->diffInHours($nextAllowedTime) + 1; // Округление до целых часов

            throw new HttpResponseException(response()->json([
                'message' => 'Вы сможете отредактировать событие через ' . $diffInHours . ' ч.',
                'hours_left' => $diffInHours,
            ], 422));
        }

        return $next($request);
    }
}