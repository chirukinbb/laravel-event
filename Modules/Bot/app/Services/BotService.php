<?php

namespace Modules\Bot\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BotService
{
    public function login(string $email, string $password): array
    {
        $response = Http::acceptJson()->post($this->baseUrl('/api/v1/login'), [
            'email' => $email,
            'password' => $password,
        ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message', 'Invalid email or password.'));
        }

        $payload = $response->json();
        $token = $payload['token'] ?? null;

        if (!$token) {
            throw new RuntimeException('Login response did not contain a token.');
        }

        Log::info('Bot login success', [
            'email' => $email,
        ]);

        return [
            'token' => $token,
        ];
    }

    public function createEvent(string $token, array $data): array
    {
        $payload = [
            'title' => $data['title'] ?? 'New Bot Event',
            'description' => $data['description'] ?? 'Created from bot flow',
            'category_id' => $data['category_id'] ?? 1,
            'thumbnail_url' => $data['thumbnail_url'] ?? 'https://example.com/images/default-event.png',
            'address' => [
                $data['coordinate_lat'] ?? 0,
                $data['coordinate_lng'] ?? 0,
            ],
            'planing_time' => $data['planing_time'] ?? now()->addDay()->timestamp,
            'slots' => (int)($data['slots'] ?? 1),
            'is_public' => (bool)($data['is_public'] ?? true),
            'condition' => $data['condition'] ?? 'all',
            'price' => $data['price'] ?? 0,
            'country_iso' => $data['country_iso'] ?? null,
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->post($this->baseUrl('/api/v1/events'), $payload);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message', 'Event creation failed.'));
        }

        $event = $response->json();

        Log::info('Bot event created via API', [
            'event_id' => $event['id'] ?? null,
            'title' => $event['title'] ?? $payload['title'],
        ]);

        return $event;
    }

    public function sendMessage(string $token, int $chatId, string $content): array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->post($this->baseUrl('/api/v1/chat/' . $chatId), [
                'content' => $content,
            ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message', 'Chat message sending failed.'));
        }

        $message = $response->json();

        Log::info('Bot chat message sent via API', [
            'chat_id' => $chatId,
            'message_id' => $message['id'] ?? null,
        ]);

        return $message;
    }

    public function subscribeToEvent(string $token, int $eventId): array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->post($this->baseUrl('/api/v1/event/' . $eventId . '/subscribe'));

        if ($response->failed()) {
            throw new RuntimeException($response->json('message', 'Subscribe failed.'));
        }

        $payload = $response->json();

        Log::info('Bot subscribed to event via API', [
            'event_id' => $eventId,
        ]);

        return $payload;
    }

    public function unsubscribeFromEvent(string $token, int $eventId): array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->delete($this->baseUrl('/api/v1/event/' . $eventId . '/unsubscribe'));

        if ($response->failed()) {
            throw new RuntimeException($response->json('message', 'Unsubscribe failed.'));
        }

        $payload = $response->json();

        Log::info('Bot unsubscribed from event via API', [
            'event_id' => $eventId,
        ]);

        return $payload;
    }

    public function commentEvent(string $token, int $eventId, int $memberId, array $commentData): array
    {
        $payload = [
            'is_happened' => (bool)($commentData['is_happened'] ?? false),
            'comment' => $commentData['comment'] ?? '',
            'mark' => (int)($commentData['mark'] ?? 0),
        ];

        $response = Http::withToken($token)
            ->acceptJson()
            ->patch($this->baseUrl('/api/v1/event/' . $eventId . '/member/' . $memberId), $payload);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message', 'Comment submit failed.'));
        }

        $body = $response->json();

        Log::info('Bot submitted event feedback via API', [
            'event_id' => $eventId,
            'member_id' => $memberId,
        ]);

        return $body;
    }

    public function handleFlow(string $email, string $password, array $eventData, string $chatMessage): array
    {
        $loginResult = $this->login($email, $password);
        $token = $loginResult['token'];
        $event = $this->createEvent($token, $eventData);

        $chatId = $event['chat_id'] ?? $event['chat']['id'] ?? null;

        if (!$chatId) {
            throw new RuntimeException('Could not resolve chat id in event API response.');
        }

        $message = $this->sendMessage($token, (int)$chatId, $chatMessage);

        return [
            'token' => $token,
            'event' => $event,
            'chat_id' => (int)$chatId,
            'message' => $message,
        ];
    }

    protected function baseUrl(string $path): string
    {
        $base = rtrim(config('app.url', 'http://localhost'), '/');

        return $base . $path;
    }
}
