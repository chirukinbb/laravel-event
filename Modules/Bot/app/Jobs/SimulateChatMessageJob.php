<?php

namespace Modules\Bot\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Bot\Services\BotService;

class SimulateChatMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int    $userId,
        public int    $chatId,
        public string $message,
    )
    {
    }

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            return;
        }

        $botService = app(BotService::class);
        $login = $botService->login($user->email, 'password');
        $botService->sendMessage($login['token'], $this->chatId, $this->message);
    }
}
