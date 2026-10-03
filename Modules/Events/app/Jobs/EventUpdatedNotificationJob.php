<?php

namespace Modules\Events\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Messaging\MulticastSendReport;
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

        if (!$event) {
            return;
        }

        $tokens = Filter::query()
            ->where('user_id', '!=', $event->user_id)
            ->whereNotNull('center')
            ->whereHas('user', fn($q) => $q->whereNotNull('fcm_token'))
            ->with('user:id,fcm_token')
            ->get()
            ->filter(fn(Filter $filter) => is_array($filter->center)
                && $this->distance(
                    $filter->center[0],
                    $filter->center[1],
                    $event->coordinate_lat,
                    $event->coordinate_lng
                ) <= $filter->radius
            )
            ->pluck('user.fcm_token')
            ->filter()
            ->unique()
            ->values();

        if ($tokens->isEmpty()) {
            return;
        }

        $message = (new EventNotification($event))->toFcm(null);
        $messaging = \Firebase::messaging();

        // FCM ограничивает multicast 500 токенами
        foreach ($tokens->chunk(500) as $chunk) {
            $report = $messaging->sendMulticast($message, $chunk->all());

            $this->cleanupInvalidTokens($report);
        }
    }

    protected function cleanupInvalidTokens(MulticastSendReport $report): void
    {
        $invalid = array_merge(
            $report->unknownTokens(),
            $report->invalidTokens()
        );

        if (!empty($invalid)) {
            User::whereIn('fcm_token', $invalid)->update(['fcm_token' => null]);
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