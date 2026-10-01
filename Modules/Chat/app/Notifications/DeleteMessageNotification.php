<?php

namespace Modules\Chat\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class DeleteMessageNotification extends Notification
{
    public function __construct(
        private int $chatId,
        private int $messageId,
    )
    {
    }

    public function via($notifiable)
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        return (new FcmMessage(notification: new FcmNotification()))->data([
            'action' => 'delete_chat_message',
            'chat_id' => (string)$this->chatId,
            'message_id' => (string)$this->messageId,
        ])->topic('chat' . $this->chatId);
    }
}
