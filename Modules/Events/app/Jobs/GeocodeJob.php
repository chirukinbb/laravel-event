<?php

namespace Modules\Events\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Event\Repositories\GeoRepository;
use Modules\Events\Models\Event;

class GeocodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $eventId)
    {
    }

    public function handle()
    {
        $event = Event::find($this->eventId);

        $address = new GeoRepository($event->address);
        $address->geocoding();

        $event->country_iso = $address->country_id;
        $event->coordinate_lat = $address->lat;
        $event->coordinate_lng = $address->lng;
    }
}