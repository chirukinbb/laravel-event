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
        return (new FcmMessage())->data(['action' => 'refresh', 'screen' => 'events']);
    }
}