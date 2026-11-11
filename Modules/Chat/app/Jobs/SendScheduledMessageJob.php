<?php

namespace Modules\Chat\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Chat\Models\Chat;

class SendScheduledMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int           $chatId,
        public int           $userId,
        public string        $content,
        public Carbon|string $sendAt,
    )
    {
    }

    public function handle(): void
    {
        $sendAt = $this->sendAt instanceof Carbon ? $this->sendAt : Carbon::parse($this->sendAt);

        if (now()->lt($sendAt)) {
            static::dispatch($this->chatId, $this->userId, $this->content, $sendAt)
                ->delay($sendAt->diffInSeconds(now()));

            return;
        }

        $chat = Chat::find($this->chatId);

        if (!$chat) {
            return;
        }

        $message = $chat->messages()->create([
            'content' => $this->content,
            'user_id' => $this->userId,
        ]);

        NewMessageJob::dispatch($message->id);
    }
}
