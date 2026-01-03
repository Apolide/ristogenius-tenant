<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'channel',
        'external_thread_id',
        'topic',
        'status',
        'last_update_at',
    ];

    protected $casts = [
        'last_update_at' => 'datetime',
    ];

    // public function contact()
    // {
    //     return $this->belongsTo(Contact::class);
    // }

    // public function messages()
    // {
    //     return $this->hasMany(Message::class);
    // }

    // public function leads()
    // {
    //     return $this->hasMany(Lead::class);
    // }


    // public function lead(): BelongsTo
    // {
    //     return $this->belongsTo(Lead::class);
    // }

    // public function contact(): BelongsTo
    // {
    //     return $this->belongsTo(Contact::class);
    // }

    // public function messages(): HasMany
    // {
    //     return $this->hasMany(Message::class);
    // }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    // public function messages(): HasMany
    // {
    //     return $this->hasMany(Message::class);
    // }

    // Se vuoi vedere i lead legati a questa conversation (il tuo schema permette 0..N lead con stesso conversation_id)
    // public function leads(): HasMany
    // {
    //     return $this->hasMany(Lead::class);
    // }


    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
