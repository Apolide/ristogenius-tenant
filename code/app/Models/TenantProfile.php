<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TenantProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'company_name',
        'city',
        'province',
        'postcode',
        'address',
        'settings',
        'message_settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'message_settings' => 'array',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);
    }
}
