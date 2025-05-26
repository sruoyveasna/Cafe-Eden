<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    public $token, $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url("http://localhost:5173/reset-password?token={$this->token}&email={$this->email}");

        return (new MailMessage)
            ->subject('Reset Password')
            ->line('Click the button below to reset your password:')
            ->action('Reset Password', $url)
            ->line('If you did not request a password reset, please ignore this email.');
    }
}
