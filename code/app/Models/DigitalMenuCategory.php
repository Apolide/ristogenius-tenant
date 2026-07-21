<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DigitalMenuCategory extends Model
{
    use HasUuids;

    protected $table = 'digital_menu_categories';

    protected $fillable = ['digital_menu_id', 'name', 'description', 'position', 'is_enabled'];

    protected $casts = ['name' => 'array', 'description' => 'array', 'is_enabled' => 'boolean'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(DigitalMenu::class, 'digital_menu_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(DigitalMenuProduct::class, 'digital_menu_category_product', 'menu_category_id', 'product_id')->withPivot(['menu_price', 'badge', 'position', 'is_visible'])->withTimestamps()->orderByPivot('position');
    }

    public function translatedName(?string $language = null): string
    {
        $language ??= app()->getLocale();

        return $this->name[$language] ?? $this->name['it'] ?? collect($this->name)->first() ?? '';
    }
}
