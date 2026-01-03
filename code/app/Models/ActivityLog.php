<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'action',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    public function user()
    {
        // which agent triggered the action (nullable)
        return $this->belongsTo(User::class);
    }
}
