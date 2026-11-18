<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;


class Notification extends Model
{
    use HasFactory;


    private const LEGACY_ORDER_KEYS = [
        'Nuovo ordine :reference' => 'public_orders.notifications.new_order_reference',
        'Consegna · :customer' => 'public_orders.notifications.delivery_customer',
        'Asporto · :customer' => 'public_orders.notifications.takeaway_customer',
        'Risposta alla proposta · :reference' => 'public_orders.notifications.proposal_response_reference',
        'Il cliente ha accettato la proposta di modifica.' => 'public_orders.notifications.staff_proposal_accepted',
        'Il cliente ha rifiutato la proposta di modifica.' => 'public_orders.notifications.staff_proposal_rejected',
        'Nuovo ordine dal tavolo' => 'public_orders.notifications.new_table_order',
        'Tavolo :table · Uscita :course di :customer da verificare e accettare.' => 'public_orders.notifications.table_course_review_message',
        'Comanda inviata dal personale' => 'orders.messages.order_sent_by_staff',
        'Tavolo :table · Uscita :course inviata ai reparti.' => 'orders.messages.table_table_course_course_sent_to_departments',
    ];

    protected $fillable = ['title', 'message', 'url', 'type', 'translation_data'];

    protected $casts = ['translation_data' => 'array'];

    public function translatedTitle(): string
    {
        $data = $this->translation_data ?: $this->legacyTranslationData();

        return isset($data['title']) ? __(self::LEGACY_ORDER_KEYS[$data['title']] ?? $data['title'], $data['parameters'] ?? []) : $this->title;
    }

    public function translatedMessage(): string
    {
        $data = $this->translation_data ?: $this->legacyTranslationData();

        return isset($data['message']) ? __(self::LEGACY_ORDER_KEYS[$data['message']] ?? $data['message'], $data['parameters'] ?? []) : $this->message;
    }

    /** Only recognize exact system-generated legacy formats; preserve free text. */
    private function legacyTranslationData(): array
    {
        if ($this->type === 'customer_order.received'
            && preg_match('/^Nuovo ordine (.+)$/u', $this->title, $title)
            && preg_match('/^(Consegna|Asporto) · (.*)$/u', $this->message, $message)) {
            return ['title' => 'Nuovo ordine :reference',
                'message' => $message[1] === 'Consegna' ? 'Consegna · :customer' : 'Asporto · :customer',
                'parameters' => ['reference' => $title[1], 'customer' => $message[2]]];
        }
        if (in_array($this->type, ['customer_order.proposal_accepted', 'customer_order.proposal_rejected'], true)
            && preg_match('/^Risposta alla proposta · (.+)$/u', $this->title, $title)
            && in_array($this->message, ['Il cliente ha accettato la proposta di modifica.', 'Il cliente ha rifiutato la proposta di modifica.'], true)) {
            return ['title' => 'Risposta alla proposta · :reference', 'message' => $this->message,
                'parameters' => ['reference' => $title[1]]];
        }
        if ($this->type === 'table_order.received' && $this->title === 'Nuovo ordine dal tavolo'
            && preg_match('/^Tavolo (.*) · Uscita ([0-9]+) di (.*) da verificare e accettare\.$/u', $this->message, $message)) {
            return ['title' => $this->title, 'message' => 'Tavolo :table · Uscita :course di :customer da verificare e accettare.',
                'parameters' => ['table' => $message[1], 'course' => $message[2], 'customer' => $message[3]]];
        }
        if ($this->type === 'staff_order.sent' && $this->title === 'Comanda inviata dal personale'
            && preg_match('/^Tavolo (.*) · Uscita ([0-9]+) inviata ai reparti\.$/u', $this->message, $message)) {
            return ['title' => $this->title, 'message' => 'Tavolo :table · Uscita :course inviata ai reparti.',
                'parameters' => ['table' => $message[1], 'course' => $message[2]]];
        }

        return [];
    }

    /**
     * I ruoli associati a questa notifica.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'notification_role');
    }

    /**
     * Gli utenti che hanno ricevuto questa notifica.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notification')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

}
