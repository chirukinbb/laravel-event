<?php

namespace Modules\Events\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Event\Repositories\GeoRepository;
use Modules\Events\Models\Event;
use Modules\Events\Notifications\EventNotification;
use Modules\Users\Models\Filter;

class EventUpdatedNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $eventId)
    {
    }

    public function handle()
    {
        $event = Event::find($this->eventId);
        $tokens = [];

        Filter::each(function (Filter $filter) use ($event, &$tokens) {
            if ($event->user_id !== $filter->user_id) {
                if (is_array($filter->center)) {
                    if ($this->distance($filter->center[0], $filter->center[1], $event->coordinate_lat, $event->coordinate_lng) <= $filter->radius) {
                        $tokens[] = $filter->user->fcm_token;
                    }
                }
            }
        });

        if (!empty($tokens)) {
            \Firebase::messaging()->subscribeToTopic('event_' . $event->id, $tokens);
            \Firebase::messaging()->send((new EventNotification($event))->toFcm($tokens));
            \Firebase::messaging()->unsubscribeFromTopic('event_' . $event->id, $tokens);
        }
    }

    private function distance($lat1, $lon1, $lat2, $lon2): float|int
    {
        $earthRadius = 6371; // км

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}