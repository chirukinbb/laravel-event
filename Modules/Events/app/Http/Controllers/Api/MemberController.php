<?php

namespace Modules\Events\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Events\Http\Requests\FeedbackRequest;
use Modules\Events\Http\Resource\EventResource;
use Modules\Events\Jobs\EventUpdatedNotificationJob;
use Modules\Events\Models\Event;
use Modules\Events\Models\Member;

class MemberController extends Controller
{
    public function index(Event $event)
    {
        // Возвращаем список участников события
        return response()->json([
            'data' => $event->members()->with('user.profile')->get()
        ]);
    }

    /**
     * Запись пользователя на событие
     */
    public function create(Event $event)
    {
        $user = auth()->user();

        // 1. Проверка на наличие свободных мест (если slots задан)
        if ($event->slots && $event->members()->count() >= $event->slots) {
            return response()->json([
                'message' => 'No available slots for this event.'
            ], 422);
        }

        // 2. Создаем или получаем существующую запись (защита от дубликатов)
        $member = $event->members()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        // 3. Обновляем событие и отправляем джобу обновления
        $event->refresh();
        EventUpdatedNotificationJob::dispatch($event->id);

        // 4. Безопасная подписка на FCM-топик чата
        $chat = $event->chat;
        if ($chat && !empty($user->fcm_token)) {
            try {
                \Firebase::messaging()->subscribeToTopic('chat' . $chat->id, [$user->fcm_token]);
            } catch (\Throwable $e) {
                Log::channel('userlog')->error("FCM Subscribe Error [Event ID: {$event->id}]: " . $e->getMessage());
            }
        }

        // 5. Логируем успешную запись
        Log::channel('userlog')->info('User joined event', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        return EventResource::make($event->load(['category', 'tags'])->loadCount('members'));
    }

    /**
     * Оставить отзыв/фидбек по событию
     */
    public function update(FeedbackRequest $request, Event $event, Member $member)
    {
        $member->update([
            'is_happened' => $request->is_happened,
            'comment' => $request->comment,
            'mark' => $request->mark,
        ]);

        EventUpdatedNotificationJob::dispatch($event->id);

        Log::channel('userlog')->info('User feedback submitted', [
            'user_id' => auth()->id(),
            'event_id' => $event->id,
            'member_id' => $member->id,
        ]);

        return response()->json([
            'message' => 'Feedback created successfully'
        ]);
    }

    /**
     * Отписка пользователя от события
     */
    public function destroy(Event $event, ?Member $member = null)
    {
        $userId = auth()->id();

        // Ищем запись участника, если не передана напрямую
        $member = $member ?? Member::where('event_id', $event->id)
                ->where('user_id', $userId)
                ->first();

        if (!$member) {
            return response()->json([
                'message' => 'Member not found or user is not participating in this event.'
            ], 404);
        }

        $fcmToken = $member->user?->fcm_token;

        // Удаляем запись
        $member->delete();

        // Перезагружаем модель и вызываем джобу
        $event->refresh();
        EventUpdatedNotificationJob::dispatch($event->id);

        // Безопасная отписка от FCM-топика чата
        $chat = $event->chat;
        if ($chat && !empty($fcmToken)) {
            try {
                \Firebase::messaging()->unsubscribeFromTopic('chat' . $chat->id, [$fcmToken]);
            } catch (\Throwable $e) {
                Log::channel('userlog')->error("FCM Unsubscribe Error [Event ID: {$event->id}]: " . $e->getMessage());
            }
        }

        // Логируем отписку
        Log::channel('userlog')->info('User left event', [
            'user_id' => $userId,
            'event_id' => $event->id,
        ]);

        return EventResource::make($event->load(['members', 'category', 'tags'])->loadCount('members'));
    }
}