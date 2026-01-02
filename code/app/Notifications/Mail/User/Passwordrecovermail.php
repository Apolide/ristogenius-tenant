<?php

namespace App\Notifications\Mail\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class Passwordrecovermail extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The name identifier.
     *
     * @var string
     */
    public $payload;

    

    /**
     * Create a new payment confirmation notification.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {


        $mailMessage = new MailMessage;
        
        if (config('app.env') != 'production'){
            $mailMessage->mailer('smtp');
        }

        $mailMessage->subject(__($this->payload['tenant_name']." - ".$this->payload['display_name']." reimposta la tua password."));
        $mailMessage->greeting(__("Ciao :display_name", ["display_name" => $this->payload['display_name']]));
        $mailMessage->line("Tu o qualcuno per te ha richiesto di reimpostare la password del tuo account su ".$this->payload['tenant_name'].". Se non sei stato tu ad effettuare la richiesta ignora semplicamente questa e-mail.");
        $mailMessage->line("Per reimpostare la tua password segui la procedura cliccando il tasto Reimposta password qui di seguito.");
        // $mailMessage->line($this->payload['storeName']." addebiterà l'importo della prenotazione sul tuo metodo di pagamento una volta che avrà accettato la tua richiesta di prenotazione.");
        // $mailMessage->line("Ti ricordiamo che puoi cancellare gratuitamente finchè non viene accettata e ufficializzata da ".$this->payload['storeName']);
        // $mailMessage->line("Cliccando sul tasto 'Dettagli' potrai vedere e gestire la tua prenotazione.");
        $mailMessage->action(__('Reimposta password'), $this->payload['url']);
        $mailMessage->line(__("Se hai bisogno di assistenza non esitare a contattarci."));
        #$mailMessage ->line(__("Per ogni richiesta di supporto contattaci tramite questo link: https://guide.puglia.it/contacts"));
        return $mailMessage;


       
        
        


            
            
    }
}
