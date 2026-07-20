<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = ['customer_id', 'booking_date', 'booking_time', 'pax', 'status', 'source', 'language', 'note', 'restaurant_note', 'seated_at', 'finalized_at'];

    protected $casts = ['booking_date' => 'date', 'seated_at' => 'datetime', 'finalized_at' => 'datetime', 'pax' => 'integer'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isWalkIn(): bool
    {
        return $this->source === 'walk-in';
    }

    public function tables(): BelongsToMany
    {
        return $this->belongsToMany(RoomTable::class, 'booking_room_table');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(BookingHistory::class)->latest();
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(MarketingFormSubmission::class);
    }

    public function eventFormSubmission(): HasOne
    {
        return $this->hasOne(MarketingFormSubmission::class)
            ->whereHas('form', fn ($query) => $query->where('type', 'event'));
    }

    public function recordHistory(string $event, ?string $description = null, array $changes = [], ?string $actor = null): void
    {
        $this->histories()->create([
            'event' => $event,
            'actor' => $actor ?? auth()->user()?->name ?? ($this->source === 'backoffice' ? 'Backoffice' : ucfirst($this->source)),
            'description' => $description,
            'changes' => $changes ?: null,
        ]);
    }

    protected static function booted(): void
    {
        static::created(fn (Booking $booking) => $booking->recordHistory('created', 'Prenotazione inserita'));
        static::updated(function (Booking $booking): void {
            $changes = collect($booking->getChanges())->except('updated_at')->mapWithKeys(fn ($value, $field) => [$field => ['from' => $booking->getOriginal($field), 'to' => $value]])->all();
            if ($changes !== []) {
                $booking->recordHistory(array_key_exists('status', $changes) ? 'status_changed' : 'updated', 'Prenotazione modificata', $changes);
            }
        });
        static::deleted(fn (Booking $booking) => $booking->recordHistory('deleted', 'Prenotazione eliminata'));
    }
}
