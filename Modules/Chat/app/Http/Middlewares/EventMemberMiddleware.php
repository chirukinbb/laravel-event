<?php

namespace Modules\Chat\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Chat\Models\Chat;

class EventMemberMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        /** @var Chat|null $chat */
        $chat = $request->route('chat');

        if (!$chat) {
            abort(404);
        }

        $userId = auth()->id();
        $chatable = $chat->chatable;

        // Проверяем: является ли пользователь владельцем chatable
        $isOwner = $chatable->user_id === $userId;

        // Проверяем: состоит ли пользователь в участниках (members)
        $isMember = $chatable->members()
            ->where('user_id', $userId)
            ->exists();

        // Если не владелец И не участник — запрещаем доступ
        if (!$isOwner && !$isMember) {
            abort(403);
        }

        return $next($request);
    }
}