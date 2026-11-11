<?php

namespace Modules\Bot\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Bot\Services\BotService;

class SimulateEventActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int   $authorId,
        public array $payload,
        public int   $subscribers = 3,
        public int   $messagesPerUser = 3,
        public int   $delaySeconds = 2,
        public int   $eventIndex = 1,
    )
    {
    }

    public function handle(): void
    {
        $author = User::find($this->authorId);

        if (!$author) {
            return;
        }

        $botService = app(BotService::class);
        $login = $botService->login($author->email, 'password');
        $event = $botService->createEvent($login['token'], $this->payload);

        $eventId = (int)($event['id'] ?? 0);
        $chatId = (int)($event['chat_id'] ?? $event['chat']['id'] ?? 0);

        if ($eventId <= 0 || $chatId <= 0) {
            return;
        }

        $participants = User::query()
            ->whereKeyNot($author->id)
            ->inRandomOrder()
            ->limit(max(0, $this->subscribers))
            ->get();

        $chatLog = is_array($this->payload['chat_log'] ?? null) ? $this->payload['chat_log'] : [];

        if ($chatLog !== []) {
            foreach ($chatLog as $index => $messageLog) {
                $content = (string)($messageLog['message'] ?? $messageLog['content'] ?? '');

                if ($content === '') {
                    continue;
                }

                $role = strtolower((string)($messageLog['role'] ?? 'participant'));
                $messageUser = $role === 'organizer'
                    ? $author
                    : ($participants[$index % max(1, $participants->count())] ?? $participants->first());

                if (!$messageUser) {
                    continue;
                }

                $job = SimulateChatMessageJob::dispatch($messageUser->id, $chatId, $content);

                if ($this->delaySeconds > 0) {
                    $job->delay(now()->addSeconds(($index + 1) * $this->delaySeconds));
                }
            }

            foreach ($participants as $offset => $participant) {
                $job = SimulateEventSubscriptionJob::dispatch(
                    $participant->id,
                    $eventId,
                    $chatId,
                    $this->messagesPerUser,
                    $this->delaySeconds,
                );

                if ($this->delaySeconds > 0) {
                    $job->delay(now()->addSeconds(($offset + 1) * $this->delaySeconds));
                }
            }

            return;
        }

        foreach ($participants as $offset => $participant) {
            $job = SimulateEventSubscriptionJob::dispatch(
                $participant->id,
                $eventId,
                $chatId,
                $this->messagesPerUser,
                $this->delaySeconds,
            );

            if ($this->delaySeconds > 0) {
                $job->delay(now()->addSeconds(($offset + 1) * $this->delaySeconds));
            }
        }
    }
}
