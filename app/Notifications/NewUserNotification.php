<?php

namespace App\Notifications;

use App\Enums\RoleEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $password;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password)
    {
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['emails'];
    }

    /**
     * Get the emails representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name'))
            ->markdown('emails.welcome', [
                'name' => $notifiable->name,
                'email' => $notifiable->email,
                'password' => $this->password,
                'actionUrl' => env('DEEP_LINK') . "?token={$notifiable->createToken(RoleEnum::USER->name)->plainTextToken}",
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
