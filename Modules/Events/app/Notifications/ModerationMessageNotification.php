<?php

namespace Modules\Events\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Events\Models\Event;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class ModerationMessageNotification extends Notification
{
    public function __construct(readonly private Event $event)
    {
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: 'Event was ' . ($this->event->approved ? 'approved' : 'rejected'),
            body: 'The reason: ' . $this->event->reason,
            image: $this->event->thumbnail_url
        )))->data(['screen' => 'single_event', 'event_id' => $this->event->id])->custom([
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
        ]);
    }
}