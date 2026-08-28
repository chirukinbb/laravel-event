<?php

namespace Modules\Events\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;

class ReservableMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $event = $request->route('event');

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        // Загружаем только count для конкретного ивента
        $event->loadCount('members');

        // Если количество участников достигло или превысило лимит мест
        if ($event->slots <= $event->members_count) {
            return response()->json([
                'message' => 'This event is not reservable',
            ], 400);
        }

        return $next($request);
    }

}