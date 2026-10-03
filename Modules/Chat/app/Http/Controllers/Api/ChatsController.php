<?php

namespace Modules\Chat\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Chat\Http\Requests\MessageRequest;
use Modules\Chat\Http\Resources\MessageResource;
use Modules\Chat\Jobs\DeleteMessageJob;
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

        return MessageResource::collection($messages);
    }

    public function create(Chat $chat, MessageRequest $request)
    {
    }

    public function store(Chat $chat, MessageRequest $request)
    {
        $message = $chat->messages()->create(array_merge($request->validated(), ['user_id' => auth()->id()]));
        NewMessageJob::dispatch($message->id);

        return MessageResource::make($message);
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
        UpdateMessageJob::dispatch($message->id);

        return MessageResource::make($message);
    }

    public function destroy(Chat $chat, Message $message)
    {
        $chatId = $message->chat_id;
        $messageId = $message->id;

        $message->delete();

        DeleteMessageJob::dispatch($chatId, $messageId);

        return true;
    }
}
