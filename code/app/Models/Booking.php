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

    protected $fillable = ['customer_id', 'booking_date', 'booking_time', 'pax', 'status', 'source', 'language', 'note', 'restaurant_note', 'seated_at', 'finalized_at', 'campaign_delivery_id'];

    protected $casts = ['booking_date' => 'date', 'seated_at' => 'datetime', 'finalized_at' => 'datetime', 'pax' => 'integer'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
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
            'actor' => $actor ?? auth()->user()?->name ?? ($this->source === 'backoffice' ? __('bookings.back_office') : ucfirst($this->source)),
            'description' => $description,
            'changes' => $changes ?: null,
        ]);
    }

    protected static function booted(): void
    {
        static::updating(function (Booking $booking): void {
            if ($booking->isDirty(['booking_date', 'pax', 'customer_id'])) {
                $usage = $booking->couponUsage()->with('assignment')->first();
                if ($usage) {
                    app(\App\Services\Marketing\CouponService::class)->check($usage->assignment->customer_id === $booking->customer_id, 'identity_conflict');
                    app(\App\Services\Marketing\CouponService::class)->check((int) ($usage->snapshot['context']['children'] ?? 0) <= $booking->pax, 'invalid_pax');
                    app(\App\Services\Marketing\CouponService::class)->eligible($usage->assignment, $booking->booking_date->toDateString(), $booking->pax);
                }
            }
        });
        static::created(fn (Booking $booking) => $booking->recordHistory('created', __('bookings.history_created')));
        static::updated(function (Booking $booking): void {
            $changes = collect($booking->getChanges())->except('updated_at')->mapWithKeys(fn ($value, $field) => [$field => ['from' => $booking->getOriginal($field), 'to' => $value]])->all();
            if ($changes !== []) {
                $booking->recordHistory(array_key_exists('status', $changes) ? 'status_changed' : 'updated', __('bookings.history_updated'), $changes);
            }
        });
        static::deleted(fn (Booking $booking) => $booking->recordHistory('deleted', __('bookings.history_deleted')));
    }
}
