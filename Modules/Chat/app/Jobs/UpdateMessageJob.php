<?php

namespace Modules\Chat\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Chat\Models\Message;
use Modules\Events\Notifications\UpdateMessageNotification;

class UpdateMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $messageId)
    {
    }

    public function handle()
    {
        $message = Message::find($this->messageId);

        $message->chat->chatable->members->each(function ($member) use ($message) {
            $member->notify(new UpdateMessageNotification($message));
        });
    }
}