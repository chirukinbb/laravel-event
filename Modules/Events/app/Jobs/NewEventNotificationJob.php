<?php

namespace Modules\Events\Jobs;

use App\Models\User;
use Firebase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Log;
use Modules\Events\Models\Event;
use Modules\Events\Notifications\EventNotification;
use Modules\Users\Models\Filter;
use Modules\Users\Traits\FilterTrait;

class NewEventNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, FilterTrait;

    public function __construct(private int $eventId)
    {
    }

    public function handle()
    {
        $event = Event::with('author.profile')->find($this->eventId);

        if (!$event) {
            return;
        }

        $authorLanguages = $event->author?->profile?->languages ?? [];
        $tokens = []; // [user_id => token]

        Filter::query()
            ->whereJsonContains('categories', $event->category_id)
            ->where('user_id', '!=', $event->user_id)
            ->with(['user:id,fcm_token', 'user.profile:id,user_id,languages'])
            ->lazyById(1000)
            ->each(function (Filter $filter) use ($event, $authorLanguages, &$tokens) {
                $user = $filter->user;

                // Токен пользователя уже собран по другому фильтру
                if (!$user || empty($user->fcm_token) || isset($tokens[$user->id])) {
                    return;
                }

                if (!is_array($filter->center) || count($filter->center) < 2) {
                    return;
                }

                if (!$this->crossed($authorLanguages, $user->profile?->languages ?? [])) {
                    return;
                }

                $distance = $this->distance(
                    $filter->center[0],
                    $filter->center[1],
                    $event->coordinate_lat,
                    $event->coordinate_lng
                );

                if ($distance <= $filter->radius) {
                    $tokens[$user->id] = (string)$user->fcm_token;
                }
            });

        if (empty($tokens)) {
            return;
        }

        $message = (new EventNotification($event))->toFcm(null); // без адресата
        $messaging = Firebase::messaging();
        $failed = false;

        foreach (array_chunk(array_values($tokens), 500) as $chunk) {
            try {
                $report = $messaging->sendMulticast($message, $chunk);
                $this->cleanupInvalidTokens($report);
            } catch (\Throwable $e) {
                $failed = true;
                Log::error('Firebase notification chunk failed', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($failed) {
            // Пусть сработает retry джоба
            throw new \RuntimeException("FCM send failed for event {$event->id}");
        }
    }

    protected function cleanupInvalidTokens(MulticastSendReport $report): void
    {
        $invalid = array_merge($report->unknownTokens(), $report->invalidTokens());

        if ($invalid) {
            User::whereIn('fcm_token', $invalid)->update(['fcm_token' => null]);
        }
    }
}