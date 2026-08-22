<?php

namespace Modules\Events\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\Events\Http\Requests\EventRequest;
use Modules\Events\Models\Category;
use Modules\Events\Models\Event;
use Modules\Events\Models\Tag;
use Modules\Events\Services\EventService;

class EventsController extends Controller
{
    public function __construct(private EventService $eventService)
    {
    }

    public function index()
    {
        $events = Event::paginate(15);

        return view('events::events.index', compact('events'));
    }

    public function create()
    {
        $categories = Category::all();
        $users = User::all();
        $tags = Tag::all();

        return view('events::events.create', compact(
            'users',
            'categories',
            'tags'
        ));
    }

    public function store(EventRequest $request)
    {
        $this->eventService->store($request->validated());

        return redirect()->route('events::index')->with('success', 'Event created!');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $event = Event::find($id);
        $categories = Category::all();
        $users = User::all();
        $tags = Tag::all();

        return view('events::events.edit', compact(
            'event',
            'users',
            'categories',
            'tags'
        ));
    }

    public function update(EventRequest $request, $id)
    {
        $event = Event::find($id);

        $this->eventService->update($request->validated(), $event);

        return redirect()->route('events::index')->with('success', 'Event updated!');
    }

    public function destroy($id)
    {
        Event::find($id)->delete();

        return redirect()->route('events::index')->with('success', 'Event deleted!');
    }
}
