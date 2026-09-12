<?php

namespace Modules\Chat\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Chat\Http\Requests\MessageRequest;
use Modules\Chat\Http\Resources\MessageResource;
use Modules\Chat\Jobs\NewMessageJob;
use Modules\Chat\Jobs\UpdateMessageJob;
use Modules\Chat\Models\Chat;
use Modules\Chat\Models\Message;

class ChatsController extends Controller
{
    public function index(Chat $chat)
    {
        $messages = $chat->messages()
            ->latest()
            ->paginate(10);

        return MessageResource::collection($messages->reverse());
    }

    public function create(Chat $chat, MessageRequest $request)
    {
    }

    public function store(Chat $chat, MessageRequest $request)
    {
        $message = $chat->messages()->create($request->validated());
        NewMessageJob::dispatch($message);

        return response()->json(true);
    }

    public function show(Chat $chat, Message $message)
    {
        return MessageResource::make($message);
    }

    public function edit(Chat $chat)
    {
    }

    public function update(Chat $chat, Message $message, MessageRequest $request)
    {
        $message->update($request->validated());
        UpdateMessageJob::dispatch($message);

        return response()->json(true);
    }

    public function destroy(Chat $chat, Message $message)
    {
        NewMessageJob::dispatch($message);
        $message->delete();

        return response()->json(true);
    }
}