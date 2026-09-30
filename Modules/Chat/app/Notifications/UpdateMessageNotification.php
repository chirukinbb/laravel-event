<?php

namespace Modules\Chat\Notifications;

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
        return (new FcmMessage(notification: new FcmNotification()))->data([
            'action' => 'update_chat_message',
            'chat_id' => (string)$this->message->chat_id,
            'message_id' => (string)$this->message->id,
        ])->topic('chat' . $this->message->chat_id);
    }
}