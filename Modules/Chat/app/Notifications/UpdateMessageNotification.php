<?php

namespace Modules\Events\Notifications;

use Illuminate\Notifications\Notification;
use Modules\Chat\Models\Message;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class UpdateMessageNotification extends Notification
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
        return (new FcmMessage(notification: new FcmNotification()))->data(['screen' => 'chat', 'chat_id' => $this->message->chat_id, 'message_id' => $this->message->id]);
    }
}