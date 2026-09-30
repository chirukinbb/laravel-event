<?php

namespace Modules\Chat\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Chat\Models\Message;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class NewMessageNotification extends Notification
{
    public function __construct(private Message $message)
    {
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $profile = $this->message->user->profile;

        return (new FcmMessage(notification: new FcmNotification(
            title: 'New Message',
            body: $profile->name . ' left message in' . $this->message->chat->chatable?->title . '`s chat',
            image: $profile->avatar_url
        )))->data(['screen' => 'chat', 'chat_id' => (string)$this->message->chat_id, 'message_id' => (string)$this->message->id])->custom([
            'android' => [
                'priority' => 'high', // Пробуждает устройство[cite: 2]
                'notification' => [
                    'channel_id' => 'high_importance', // Канал с MAX приоритетом на клиенте
                    'notification_priority' => 'PRIORITY_MAX', // Принудительно заставляет Android выкатить баннер
                    'default_sound' => true,
                    'default_vibrate_timings' => true,
                ],
            ],
            'apns' => [
                'payload' => [
                    'aps' => [
                        'sound' => 'default',
                        'content-available' => 1,
                    ],
                ],
            ],
        ])->topic('chat' . $this->message->chat_id);
    }
}