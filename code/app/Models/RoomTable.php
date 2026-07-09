<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomTable extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'settings_room_tables';

    protected $fillable = [
        'name',
        'type',
        'room_id',
        'min_people',
        'max_people',
        'status',
        'x',
        'y',
        'w',
        'h',
        'rotation',
    ];

    protected $casts = [
        'min_people' => 'integer',
        'max_people' => 'integer',
        'x' => 'integer',
        'y' => 'integer',
        'w' => 'integer',
        'h' => 'integer',
        'rotation' => 'integer',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
