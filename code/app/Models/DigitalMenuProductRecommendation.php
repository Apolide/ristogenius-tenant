<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DigitalMenuProductRecommendation extends Model
{
    public const UPSELL = 'upsell';

    public const CROSS_SELL = 'cross_sell';

    protected $table = 'digital_menu_product_recommendations';

    protected $fillable = ['product_id', 'recommended_product_id', 'type', 'message', 'placement', 'position', 'is_active'];

    protected $casts = ['message' => 'array', 'is_active' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(DigitalMenuProduct::class, 'product_id');
    }

    public function recommendedProduct(): BelongsTo
    {
        return $this->belongsTo(DigitalMenuProduct::class, 'recommended_product_id');
    }
}
