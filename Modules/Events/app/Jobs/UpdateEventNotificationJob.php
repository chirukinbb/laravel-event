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
use Modules\Events\Notifications\RefreshNotification;
use Modules\Users\Models\Filter;
use Modules\Users\Traits\FilterTrait;

class UpdateEventNotificationJob implements ShouldQueue
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

        $tokens = []; // [user_id => token]

        // 1. Участники события получают refresh всегда
        $event->members()
            ->where('users.id', '!=', $event->user_id)
            ->whereNotNull('users.fcm_token')
            ->get(['users.id', 'users.fcm_token'])
            ->each(function ($member) use (&$tokens) {
                $tokens[$member->id] = (string)$member->fcm_token;
            });

        // 2. Остальные: по фильтрам (категория + язык + радиус)
        $authorLanguages = $event->author?->profile?->languages ?? [];

        Filter::query()
            ->whereJsonContains('categories', $event->category_id)
            ->where('user_id', '!=', $event->user_id)
            ->with(['user:id,fcm_token', 'user.profile:id,user_id,languages'])
            ->lazyById(1000)
            ->each(function (Filter $filter) use ($event, $authorLanguages, &$tokens) {
                $user = $filter->user;

                // Нет юзера, нет токена или уже в списке (участник / другой фильтр)
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

        $message = (new RefreshNotification($event))->toFcm(null); // без адресата
        $messaging = Firebase::messaging();
        $failed = false;

        foreach (array_chunk(array_values($tokens), 500) as $chunk) {
            try {
                $report = $messaging->sendMulticast($message, $chunk);
                $this->cleanupInvalidTokens($report);
            } catch (\Throwable $e) {
                $failed = true;
                Log::error("FCM Event Notification Error [Event ID: {$event->id}]", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($failed) {
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