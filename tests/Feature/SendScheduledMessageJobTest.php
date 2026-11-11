<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Modules\Chat\Jobs\SendScheduledMessageJob;
use Modules\Chat\Models\Chat;
use Tests\TestCase;

class SendScheduledMessageJobTest extends TestCase
{
    public function test_it_creates_scheduled_chat_message_when_time_has_come(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $chat = Chat::create([
            'chatable_type' => User::class,
            'chatable_id' => $user->id,
        ]);

        $job = new SendScheduledMessageJob(
            chatId: $chat->id,
            userId: $user->id,
            content: 'Hello from scheduler',
            sendAt: Carbon::now()->subSecond(),
        );

        $job->handle();

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'content' => 'Hello from scheduler',
        ]);
    }
}
