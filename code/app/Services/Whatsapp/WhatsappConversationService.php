<?php

namespace App\Services\Whatsapp;

use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\Meta\MetamodelService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsappConversationService
{
    /**
     * Gestisce un nuovo messaggio in entrata dal webhook.
     *
     * @param array $value = $requestData["entry"][0]["changes"][0]["value"]
     */
    // app/Services/Whatsapp/WhatsappConversationService.php

    public function storeIncomingMessage(array $value): ?WhatsappMessage
    {
        try {
            $metadata = $value['metadata'] ?? [];
            $contact  = $value['contacts'][0] ?? [];
            $message  = $value['messages'][0] ?? [];

            $waId  = $contact['wa_id'] ?? null;
            $name  = $contact['profile']['name'] ?? null;
            $phoneNumberId       = $metadata['phone_number_id'] ?? null;
            $displayPhoneNumber  = $metadata['display_phone_number'] ?? null;

            if (! $waId || ! $message) {
                return null;
            }

            $waMessageId = $message['id'] ?? null;

            // Idempotenza
            if ($waMessageId) {
                $existing = WhatsappMessage::where('wa_message_id', $waMessageId)->first();
                if ($existing) {
                    return $existing;
                }
            }

            $conversation = WhatsappConversation::firstOrCreate(
                [
                    'contact_wa_id'             => $waId,
                    'business_phone_number_id'  => $phoneNumberId,
                ],
                [
                    'contact_name'         => $name,
                    'display_phone_number' => $displayPhoneNumber,
                    'status'               => 'open',
                    'last_message_at'      => now('Europe/Rome'),
                    'last_message_text'    => $message['text']['body'] ?? null,
                ]
            );

            // 👇 qui la conversione corretta UTC → ora italiana
            $timestamp = isset($message['timestamp'])
                ? Carbon::createFromTimestamp((int) $message['timestamp'], 'UTC')
                    ->setTimezone('Europe/Rome')
                : now('Europe/Rome');

            $conversation->update([
                'contact_name'      => $name ?? $conversation->contact_name,
                'last_message_at'   => $timestamp,
                'last_message_text' => $message['text']['body'] ?? $conversation->last_message_text,
            ]);

            $whatsappMessage = $conversation->messages()->create([
                'direction'    => 'in',
                'wa_message_id'=> $waMessageId,
                'type'         => $message['type'] ?? 'text',
                'body'         => $message['text']['body'] ?? null,
                'raw_payload'  => $message,
                'sent_at'      => $timestamp,
            ]);

            return $whatsappMessage;
        } catch (\Throwable $e) {
            Log::error('Errore storeIncomingMessage WhatsApp: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }



    /**
     * Invio di un messaggio di testo verso il contatto,
     * salvataggio della conversazione e del messaggio in uscita.
     */
    public function sendOutgoingMessage(WhatsappConversation $conversation, string $text): WhatsappMessage
    {
        // Invio tramite il tuo service Meta
        MetamodelService::sendTextMessage(
            $conversation->contact_wa_id, // es. 393925000100
            $text
        );

        $now = now();

        // Aggiorna la conversazione
        $conversation->update([
            'last_message_at'   => $now,
            'last_message_text' => $text,
        ]);

        // Registra il messaggio in uscita
        $message = $conversation->messages()->create([
            'direction'    => 'out',
            'wa_message_id'=> null, // se vuoi puoi farti ritornare l'id dal MetaService
            'type'         => 'text',
            'body'         => $text,
            'raw_payload'  => null, // potresti salvare il payload dell'API se il tuo MetaService lo restituisce
            'sent_at'      => $now,
        ]);

        return $message;
    }
}
