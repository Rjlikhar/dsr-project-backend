<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApiPasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(private string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset your password')
            ->greeting('Hello,')
            ->line('Use this token to reset your password:')
            ->line($this->token)
            ->line('Enter this token with your email and new password in the reset password form.')
            ->line('If you did not request this, you can ignore this email.');
    }
}