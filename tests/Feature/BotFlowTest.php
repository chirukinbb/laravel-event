<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Modules\Bot\Services\BotService;
use Tests\TestCase;

class BotFlowTest extends TestCase
{
    public function test_bot_service_can_login_create_event_and_send_message_via_api(): void
    {
        Http::fake([
            'http://localhost/api/v1/login' => Http::response([
                'token' => 'bot-token-123',
            ], 200),
            'http://localhost/api/v1/events' => Http::response([
                'id' => 42,
                'title' => 'Bot event',
                'chat_id' => 99,
            ], 200),
            'http://localhost/api/v1/chat/99' => Http::response([
                'id' => 500,
                'chat_id' => 99,
                'user_id' => 7,
                'content' => 'Hi team!',
            ], 200),
            'http://localhost/api/v1/event/42/subscribe' => Http::response([
                'message' => 'Subscribed successfully',
                'event_id' => 42,
            ], 200),
            'http://localhost/api/v1/event/42/member/7' => Http::response([
                'message' => 'Feedback created successfully',
                'event_id' => 42,
                'member_id' => 7,
            ], 200),
        ]);

        $service = new BotService();

        $loginResult = $service->login('bot@example.com', 'secret123');
        $this->assertSame('bot-token-123', $loginResult['token']);

        $event = $service->createEvent('bot-token-123', [
            'title' => 'Bot event',
            'description' => 'Hello from bot',
            'category_id' => 1,
            'thumbnail_url' => 'https://example.com/thumbnail.png',
            'coordinate_lat' => 50.123,
            'coordinate_lng' => 30.456,
            'country_iso' => 'US',
            'planing_time' => now()->addDay()->timestamp,
            'slots' => 10,
            'is_public' => true,
        ]);

        $this->assertSame(42, $event['id']);
        $this->assertSame(99, $event['chat_id']);

        $message = $service->sendMessage('bot-token-123', 99, 'Hi team!');

        $this->assertSame('Hi team!', $message['content']);
        $this->assertSame(99, $message['chat_id']);

        $subscription = $service->subscribeToEvent('bot-token-123', 42);
        $this->assertSame('Subscribed successfully', $subscription['message']);

        $comment = $service->commentEvent('bot-token-123', 42, 7, [
            'is_happened' => true,
            'comment' => 'Amazing event',
            'mark' => 9,
        ]);

        $this->assertSame('Feedback created successfully', $comment['message']);
    }
}
