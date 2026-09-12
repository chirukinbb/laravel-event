<?php

namespace Modules\Chat\Http\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Modules\Chat\Models\Message;

class MessageAuthorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /**
         * @var Message $message
         */
        $message = $request->route('message');

        if ($message->user_id !== auth()->id())
            return response()->json(['message' => 'You are not authorized to access this resource'], 403);

        return $next($request);
    }
}