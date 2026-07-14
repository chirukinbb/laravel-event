<?php

namespace Modules\Events\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Events\Models\Event;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class EventNotification extends Notification
{
    public function __construct(private Event $event)
    {
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification(
            title: 'New event',
            body: 'New event will be in your region.',
            image: asset($this->event->thumbnail_url)
        )))->data(['screen' => 'single_event', 'event_id' => $this->event->id]);
    }
}