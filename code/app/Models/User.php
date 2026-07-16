<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasRoles;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'lang',
        'receive_whatsapp_notifications',
        'receive_telegram_notifications',
        'password',
        'enabled',
        'invited_at',
        'activated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'invited_at' => 'datetime',
            'activated_at' => 'datetime',
            'enabled' => 'boolean',
            'receive_whatsapp_notifications' => 'boolean',
            'receive_telegram_notifications' => 'boolean',
            'password' => 'hashed',
        ];
    }



    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function ownedLeads()
    {
        return $this->hasMany(Lead::class, 'owner_id');
    }


    /**
     * Le notifiche ricevute dall'utente.
     */
    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'user_notification')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

    /**
     * Notifiche non ancora lette dall'utente.
     */
    public function unreadNotifications()
    {
        return $this->notifications()->wherePivot('read_at', null);
    }

    public function getUnreadNotificationsAttribute()
    {
        return $this->notifications()->wherePivot('read_at', null)->orderBy('notifications.created_at', 'desc')->get();
    }

    /**
     * Le operazioni ricevute dall'utente.
     */
    public function operations()
    {
        return $this->belongsToMany(Operation::class, 'user_operation')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

}
