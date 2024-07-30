<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $url;

    /**
     * Create a new notification instance.
     *
     * @param string $url
     * @return void
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Get the channels the notification should be sent on.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->view('emails.reset_password', ['url' => $this->url])
            ->subject('비밀번호 재설정 요청');
    }
}
