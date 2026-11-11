<?php

namespace Modules\Bot\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Bot\Services\BotService;

class BotController extends Controller
{
    public function __construct(private BotService $botService)
    {
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        return response()->json($this->botService->login($data['email'], $data['password']));
    }

    public function flow(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'min:1'],
            'planing_time' => ['required', 'date'],
            'message' => ['required', 'string'],
            'thumbnail_url' => ['nullable', 'string'],
            'coordinate_lat' => ['nullable', 'numeric'],
            'coordinate_lng' => ['nullable', 'numeric'],
            'country_iso' => ['nullable', 'string', 'max:10'],
            'slots' => ['nullable', 'integer', 'min:1'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $result = $this->botService->handleFlow(
            $data['email'],
            $data['password'],
            [
                'title' => $data['title'],
                'description' => $data['description'] ?? 'Created from bot flow',
                'category_id' => $data['category_id'],
                'planing_time' => $data['planing_time'],
                'thumbnail_url' => $data['thumbnail_url'] ?? 'https://example.com/default-event.png',
                'coordinate_lat' => $data['coordinate_lat'] ?? 0,
                'coordinate_lng' => $data['coordinate_lng'] ?? 0,
                'country_iso' => $data['country_iso'] ?? null,
                'slots' => $data['slots'] ?? 1,
                'is_public' => $data['is_public'] ?? true,
            ],
            $data['message']
        );

        return response()->json([
            'token' => $result['token'],
            'event' => $result['event'],
            'chat_id' => $result['chat_id'],
            'message' => $result['message'],
        ]);
    }

    public function createEvent(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'integer', 'min:1'],
            'planing_time' => ['required', 'date'],
            'thumbnail_url' => ['nullable', 'string'],
            'coordinate_lat' => ['nullable', 'numeric'],
            'coordinate_lng' => ['nullable', 'numeric'],
            'country_iso' => ['nullable', 'string', 'max:10'],
            'slots' => ['nullable', 'integer', 'min:1'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($this->botService->createEvent($token, $data));
    }

    public function sendMessage(Request $request, int $chat)
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($this->botService->sendMessage($token, $chat, $data['content']));
    }

    public function subscribe(Request $request, int $event)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($this->botService->subscribeToEvent($token, $event));
    }

    public function unsubscribe(Request $request, int $event)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($this->botService->unsubscribeFromEvent($token, $event));
    }

    public function comment(Request $request, int $event, int $member)
    {
        $data = $request->validate([
            'is_happened' => ['required', 'boolean'],
            'comment' => ['required', 'string'],
            'mark' => ['required', 'integer', 'min:0', 'max:10'],
        ]);

        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json($this->botService->commentEvent($token, $event, $member, $data));
    }
}

