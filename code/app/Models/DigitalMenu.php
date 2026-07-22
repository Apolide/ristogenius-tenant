<?php

namespace App\Models;

use App\Models\Concerns\SearchesTranslatedName;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalMenu extends Model
{
    use HasUuids, SearchesTranslatedName;

    protected $table = 'digital_menu_menus';

    protected $fillable = ['slug', 'name', 'description', 'disclaimer', 'enabled_languages', 'theme', 'image_path', 'service_charge', 'position', 'is_published', 'is_visible'];

    protected $casts = ['name' => 'array', 'description' => 'array', 'disclaimer' => 'array', 'enabled_languages' => 'array', 'theme' => 'array', 'service_charge' => 'decimal:2', 'is_published' => 'boolean', 'is_visible' => 'boolean'];

    public function categories(): HasMany
    {
        return $this->hasMany(DigitalMenuCategory::class, 'digital_menu_id')->orderBy('position');
    }

    public function translatedName(?string $language = null): string
    {
        $language ??= app()->getLocale();

        return $this->name[$language] ?? $this->name['it'] ?? collect($this->name)->first() ?? '';
    }

    public function translatedDescription(?string $language = null): string
    {
        $language ??= app()->getLocale();

        return $this->description[$language] ?? $this->description['it'] ?? '';
    }
}
