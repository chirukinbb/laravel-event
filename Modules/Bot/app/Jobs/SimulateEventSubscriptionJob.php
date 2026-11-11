<?php

namespace Modules\Bot\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Bot\Services\BotService;

class SimulateEventSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId,
        public int $eventId,
        public int $chatId,
        public int $messagesPerUser = 3,
        public int $delaySeconds = 2,
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
        $botService->subscribeToEvent($login['token'], $this->eventId);

        for ($i = 0; $i < $this->messagesPerUser; $i++) {
            $message = $this->buildMessage($user->name ?? 'Guest', $i);
            $job = SimulateChatMessageJob::dispatch($user->id, $this->chatId, $message);

            if ($this->delaySeconds > 0) {
                $job->delay(now()->addSeconds(($i + 1) * $this->delaySeconds));
            }
        }
    }

    protected function buildMessage(string $userName, int $index): string
    {
        $samples = [
            sprintf('%s: классно, обязательно приду!', $userName),
            sprintf('%s: кто еще едет? хочу в компанию!', $userName),
            sprintf('%s: когда стартуем и где встречаемся?', $userName),
            sprintf('%s: отличный формат, очень хочу поучаствовать!', $userName),
            sprintf('%s: есть ли свободные места?', $userName),
            sprintf('%s: скиньте линки и детали по плану!', $userName),
        ];

        return $samples[$index % count($samples)];
    }
}
