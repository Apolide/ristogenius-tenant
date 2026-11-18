<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'name_translations',
        'production',
        'use_printer',
        'printer_number',
    ];

    protected $casts = [
        'name_translations' => 'array',
        'production' => 'boolean',
        'use_printer' => 'boolean',
        'printer_number' => 'integer',
    ];

    public function translatedName(?string $language = null): string
    {
        $language = strtolower($language ?? app()->getLocale());
        $translated = $this->name_translations[$language] ?? null;

        return is_string($translated) && trim($translated) !== '' ? $translated : $this->name;
    }
}
