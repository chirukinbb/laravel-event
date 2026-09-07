<?php

namespace Modules\Events\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;

class RefreshNotification extends Notification
{
    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage())->data(['action' => 'refresh', 'screen' => 'events'])->custom([
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
        ]);;
    }
}