<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MessageOutbox extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'event_type',
        'aggregate_type',
        'aggregate_id',
        'deduplication_key',
        'payload',
        'status',
        'attempts',
        'available_at',
        'processing_at',
        'published_at',
        'last_error',
    ];

    protected $casts = [
        'payload' => 'array',
        'available_at' => 'datetime',
        'processing_at' => 'datetime',
        'published_at' => 'datetime',
        'attempts' => 'integer',
    ];
}
