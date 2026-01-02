<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappConversation extends Model
{
    protected $fillable = [
        'contact_wa_id',
        'contact_name',
        'business_phone_number_id',
        'display_phone_number',
        'status',
        'last_message_at',
        'last_message_text',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'conversation_id')
            ->orderBy('sent_at')
            ->orderBy('id');
    }
}
