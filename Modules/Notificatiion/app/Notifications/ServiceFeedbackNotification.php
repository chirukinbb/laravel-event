<?php

namespace Modules\Notification\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class ServiceFeedbackNotification extends Notification
{
    public function __construct()
    {
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: 'Left a Service Feedback',
        )))->data(['screen' => 'service_feedback'])->custom([
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