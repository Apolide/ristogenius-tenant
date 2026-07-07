<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Lead extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'contact_id',
        'conversation_id',
        'status',
        'source',
        'title',
        'notes',
        'owner_id',
    ];

    protected $casts = [
        //
    ];

    public function owner()
    {
        // CRM agent that owns the lead
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class, 'lead_id')->latestOfMany('sent_at');
    }

}
