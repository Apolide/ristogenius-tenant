<?php

namespace App\Notifications\Mail\User;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmployeeInvitation extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Attiva il tuo account '.config('tenant.name'))
            ->greeting('Ciao '.$notifiable->name.',')
            ->line('È stato creato per te un account su '.config('tenant.name').'.')
            ->line('Imposta la password per attivarlo e accedere al sistema.')
            ->action('Attiva account', $url)
            ->line('Se non ti aspettavi questo invito, puoi ignorare questa email.');
    }
}
