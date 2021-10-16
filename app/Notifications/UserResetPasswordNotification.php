<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserResetPasswordNotification extends Notification
{
    public function __construct( $name, $token ) {
        $this->name = $name;
        $this->token = $token;
    }

    public function via( $notifiable ): array {
        return ['mail'];
    }

    public function toMail( $notifiable ): MailMessage {
        return (new MailMessage)->from('youngeek@mail.ru', 'Служба поддержки Youngeek')
                                ->subject('Youngeek. Сброс пароля')->greeting('Уважаемый ' . $this->name . '!')
                                ->line('Вы запросили сброс пароля')
                                ->action('Сбросить пароль', url('/reset-password?token=') . $this->token)
                                ->salutation('Всего доброго!');
    }

    public function toArray( $notifiable ): array {
        return [//
        ];
    }
}
