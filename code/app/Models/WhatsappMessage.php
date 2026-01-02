<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'direction',
        'wa_message_id',
        'type',
        'body',
        'raw_payload',
        'sent_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversation::class, 'conversation_id');
    }
}
