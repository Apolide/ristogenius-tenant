<?php

namespace App\Models;

use App\Models\Concerns\SearchesTranslatedName;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DigitalMenuIngredient extends Model
{
    use HasUuids, SearchesTranslatedName;

    protected $table = 'digital_menu_ingredients';

    protected $fillable = ['name', 'unit', 'stock_quantity', 'minimum_quantity', 'add_price', 'remove_price', 'tracked', 'is_frozen'];

    protected $casts = ['name' => 'array', 'stock_quantity' => 'decimal:3', 'minimum_quantity' => 'decimal:3', 'add_price' => 'decimal:2', 'remove_price' => 'decimal:2', 'tracked' => 'boolean', 'is_frozen' => 'boolean'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(DigitalMenuProduct::class, 'digital_menu_ingredient_product', 'ingredient_id', 'product_id')->withPivot('quantity');
    }

    public function translatedName(?string $language = null): string
    {
        $language ??= app()->getLocale();

        return $this->name[$language] ?? $this->name['it'] ?? collect($this->name)->first() ?? '';
    }
}
