<?php

namespace Modules\Events\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
        $event = Event::find($this->eventId);
        $tokens = [];

        Filter::whereJsonContains('categories', $event->category_id)->each(function (Filter $filter) use ($event, &$tokens) {
            if ($this->crossed($event->author->profile->languages, $filter->user->profile->languages)) {
                if ($event->user_id !== $filter->user_id) {
                    if ($event->members->contains($filter->user_id)) {
                        $tokens[] = $filter->user->fcm_token;
                    }

                    if (is_array($filter->center)) {
                        if ($this->distance($filter->center[0], $filter->center[1], $event->coordinate_lat, $event->coordinate_lng) <= $filter->radius) {
                            $tokens[] = $filter->user->fcm_token;
                        }
                    }
                }
            }
        });

        if (!empty($tokens)) {
            \Firebase::messaging()->subscribeToTopic('event_' . $event->id, $tokens);
            \Firebase::messaging()->send((new RefreshNotification($event))->toFcm($tokens));
            \Firebase::messaging()->unsubscribeFromTopic('event_' . $event->id, $tokens);
        }
    }
}