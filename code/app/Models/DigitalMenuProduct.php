<?php

namespace App\Models;

use App\Models\Concerns\SearchesTranslatedName;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalMenuProduct extends Model
{
    use HasUuids, SearchesTranslatedName;

    protected $table = 'digital_menu_products';

    protected $fillable = ['name', 'description', 'price', 'vat', 'department_id', 'image_path', 'allergens', 'is_active', 'disallow_takeaway', 'daily_pieces'];

    protected $casts = ['name' => 'array', 'description' => 'array', 'price' => 'decimal:2', 'allergens' => 'array', 'is_active' => 'boolean', 'disallow_takeaway' => 'boolean'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(DigitalMenuIngredient::class, 'digital_menu_ingredient_product', 'product_id', 'ingredient_id')->withPivot('quantity');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(DigitalMenuProductRecommendation::class, 'product_id')->orderBy('position');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(DigitalMenuCategory::class, 'digital_menu_category_product', 'product_id', 'menu_category_id')->withPivot(['menu_price', 'badge', 'position', 'is_visible'])->withTimestamps();
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
