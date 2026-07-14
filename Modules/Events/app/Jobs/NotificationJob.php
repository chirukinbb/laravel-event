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
use Modules\Events\Notifications\RefreshNotification;
use Modules\Users\Models\Filter;

class NotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $eventId)
    {
    }

    public function handle()
    {
        $event = Event::find($this->eventId);

        Filter::each(function (Filter $filter) use ($event) {
            if ($event->user_id !== $filter->user_id) {
                $filter->user->notify(new RefreshNotification());
                if ($this->distance($filter->center[0], $filter->center[1], $event->coordinate_lat, $event->coordinate_lng) <= $filter->radius) {
                    $filter->user->notify(new EventNotification($event));
                }
            }
        });
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