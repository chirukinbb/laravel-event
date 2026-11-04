<?php

namespace Modules\Events\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Events\Http\Requests\EventRequest;
use Modules\Events\Http\Resource\CommentResource;
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

        $lat = $filter->center[0] ?? null;
        $lng = $filter->center[1] ?? null;
        $userLanguages = $user->profile?->languages ?? [];

        $query = Event::query()
            ->select('events.*');

        // Расстояние (формула Haversine), в км
        if ($lat && $lng) {
            $distanceSql = '(6371 * acos(least(1, greatest(-1,
            cos(radians(?)) * cos(radians(coordinate_lat)) * cos(radians(coordinate_lng) - radians(?))
            + sin(radians(?)) * sin(radians(coordinate_lat))
        ))))';

            $query->selectRaw("ROUND($distanceSql) as distance_km", [$lat, $lng, $lat]);
        }

        $query
            // 1. Фильтр по категориям
            ->whereIn('category_id', $filter->categories)
            ->whereNot('user_id', $user->id)
            ->where('planing_time', '>', now()->subHour())
            ->whereDoesntHave('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })

            // 2. Фильтр по совпадению хотя бы одного языка автора и пользователя
            ->when(!empty($userLanguages), function ($q) use ($userLanguages) {
                $q->whereHas('author.profile', function ($profileQuery) use ($userLanguages) {
                    $profileQuery->whereRaw(
                        'JSON_OVERLAPS(languages, ?)',
                        [json_encode(array_values($userLanguages))]
                    );
                });
            })
            ->with(['category', 'tags'])
            ->withCount('members')
            ->whereApproved(true);

        // 3. Сортировка: от ближнего к дальнему, затем по времени
        if ($lat && $lng) {
            $query->orderByRaw("$distanceSql asc", [$lat, $lng, $lat]);
        }
        $query->orderBy('planing_time');

        return EventCollection::collection($query->paginate());
    }

    public function organizing(Request $request)
    {
        $events = Event::where('user_id', $request->user()->id)
            ->where('planing_time', $request->get('status') === 'archive' ? '<' : '>', now())
            ->with(['members', 'category', 'tags'])
            ->withCount('members')
            ->orderBy('planing_time')
            ->paginate();

        return EventCollection::collection($events);
    }

    public function attending(Request $request)
    {
        $events = Event::whereRelation('members', 'user_id', $request->user()->id)
            ->where('planing_time', $request->get('status') === 'archive' ? '<' : '>', now())
            ->with(['category', 'tags'])
            ->withCount('members')
            ->orderBy('planing_time')
            ->paginate();

        return EventCollection::collection($events);
    }

    public function show(Event $event)
    {
        return EventResource::make($event);
    }

    public function store(EventRequest $request)
    {
        $event = $this->eventService->store(array_merge($request->validated(), ['user_id' => $request->user()->id]));

        return EventResource::make($event);
    }

    public function update(EventRequest $request, Event $event)
    {
        $this->eventService->update($request->validated(), $event);

        return EventResource::make($event);
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            'message' => 'Event deleted successfully'
        ], 200);
    }

    public function comments(Event $event): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return CommentResource::collection($event->members()->where('is_happened', true)->paginate());
    }
}