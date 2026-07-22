<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait SearchesTranslatedName
{
    public function scopeWhereTranslatedNameContains(Builder $query, string $search): Builder
    {
        $term = mb_strtolower(trim($search));

        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term): void {
            foreach (['it', 'en', 'de'] as $language) {
                $query->orWhereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.{$language}'))) LIKE ?",
                    ['%'.$term.'%'],
                );
            }
        });
    }
}
