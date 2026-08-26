<?php

namespace Modules\Events\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Events\Http\Requests\EventRequest;
use Modules\Events\Http\Resource\EventCollection;
use Modules\Events\Http\Resource\EventResource;
use Modules\Events\Models\Event;
use Modules\Events\Services\EventService;

class EventsController extends Controller
{
    public function __construct(private EventService $eventService)
    {
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $user = $request->user();
        $filter = $user->filter;

        $lat = $filter->center[0];
        $lng = $filter->center[1];
        $radiusInKm = $filter->radius;
        $userLanguages = $user->profile?->languages ?? [];

        $events = Event::query()
            // 1. Фильтр по категориям
            ->whereIn('category_id', $filter->categories)
            ->whereNot('user_id', $user->id)
            ->whereRelation('members', function ($query) use ($user) {
                $query->whereNot('user_id', $user->id);
            })

            // 2. Фильтр по расстоянию (Haversine formula)
            ->when($lat && $lng, function ($query) use ($lat, $lng, $radiusInKm) {
                $query->whereRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(coordinate_lat)) * cos(radians(coordinate_lng) - radians(?)) + sin(radians(?)) * sin(radians(coordinate_lat)))) <= ?',
                    [$lat, $lng, $lat, $radiusInKm]
                );
            })

            // 3. Фильтр по совпадению хотя бы одного языка автора и пользователя
            ->when(!empty($userLanguages), function ($query) use ($userLanguages) {
                $query->whereHas('author.profile', function ($profileQuery) use ($userLanguages) {
                    // MySQL 8.0+: проверяет пересечение массивов JSON
                    $profileQuery->whereRaw(
                        'JSON_OVERLAPS(languages, ?)',
                        [json_encode(array_values($userLanguages))]
                    );
                });
            })
            ->with(['members', 'category', 'tags'])
            ->latest()
            ->paginate();

        return EventCollection::make($events);
    }

    public function organizing(Request $request)
    {
        $events = Event::where('user_id', $request->user()->id)
            ->with(['members', 'category', 'tags'])
            ->latest()
            ->paginate();

        return EventCollection::make($events);
    }

    public function attending(Request $request)
    {
        $events = Event::whereRelation('members', 'user_id', $request->user()->id)
            ->with(['members', 'category', 'tags'])
            ->latest()
            ->paginate();

        return EventCollection::make($events);
    }

    public function show(Event $event)
    {
        return EventResource::make($event);
    }

    public function store(EventRequest $request)
    {
        $this->eventService->store($request->validated());

        return response()->json([
            'message' => 'Event created successfully'
        ], 201);
    }

    public function update(EventRequest $request, Event $event)
    {
        $this->eventService->update($request->validated(), $event);

        return response()->json([
            'message' => 'Event updated successfully'
        ], 200);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ], 200);
    }
}