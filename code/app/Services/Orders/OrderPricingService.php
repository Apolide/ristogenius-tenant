<?php

namespace App\Services\Orders;

use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\OrderCourse;
use App\Models\OrderItem;
use Illuminate\Validation\ValidationException;

class OrderPricingService
{
    public function modifiers(DigitalMenuProduct $product, int $base, array $added, array $removed, ?OrderItem $item = null): array
    {
        $this->check(! array_intersect($added, $removed), 'Un ingrediente non può essere aggiunto e rimosso insieme.');
        $price = $base;
        $modifiers = [];
        $snapshots = $item?->modifiers()->get() ?? collect();
        foreach ($snapshots->whereNull('ingredient_id') as $snapshot) {
            $price += $snapshot->price_cents;
            $modifiers[] = $snapshot->only(['ingredient_id', 'ingredient_name', 'action', 'price_cents']);
        }
        foreach (['add' => $added, 'remove' => $removed] as $action => $ids) {
            foreach ($ids as $id) {
                $snapshot = $snapshots->first(fn ($m) => $m->ingredient_id === $id && $m->action === $action);
                if ($snapshot) {
                    $price += $snapshot->price_cents;
                    $modifiers[] = $snapshot->only(['ingredient_id', 'ingredient_name', 'action', 'price_cents']);

                    continue;
                }
                $ingredient = DigitalMenuIngredient::findOrFail($id);
                $this->check($action !== 'remove' || $product->ingredients->contains('id', $id), 'Ingrediente non presente nella ricetta.');
                $delta = OrderService::cents((string) ($action === 'add' ? $ingredient->add_price : $ingredient->remove_price));
                $price += $delta;
                $modifiers[] = ['ingredient_id' => $id, 'ingredient_name' => $ingredient->translatedName(), 'action' => $action, 'price_cents' => $delta];
            }
        }
        $this->check($price >= 0, 'Il prezzo non può essere negativo.');

        return [$price, $modifiers];
    }

    public function quote(array $line, bool $public = false, string $fulfillment = 'dine_in', ?string $language = null): array
    {
        \Illuminate\Support\Facades\Validator::make($line, [
            'category_id' => 'required|uuid', 'product_id' => 'required|uuid', 'quantity' => $public ? 'required|integer|min:1|max:100' : 'required|integer|min:1|max:1000',
            'notes' => 'nullable|string|max:2000', 'added' => 'array|max:100', 'removed' => 'array|max:100',
            'added.*' => 'uuid|distinct', 'removed.*' => 'uuid|distinct',
        ])->validate();
        $category = DigitalMenuCategory::with('menu')->findOrFail($line['category_id']);
        $menu = $category->menu;
        $this->check($category->is_enabled && $menu->is_visible && (! $public || ($menu->is_published && in_array($language, $menu->enabled_languages, true))), 'Menu non disponibile.');
        $product = $category->products()->with(['ingredients', 'department'])->where('digital_menu_products.id', $line['product_id'])->firstOrFail();
        $this->check($product->is_active && $product->pivot->is_visible, 'Prodotto non disponibile.');
        $this->check(! $public || $fulfillment === 'dine_in' || ! $product->disallow_takeaway, 'Questo prodotto è disponibile soltanto al tavolo.');
        $base = OrderService::cents((string) ($product->pivot->menu_price ?? $product->price));
        [$price,$modifiers] = $this->modifiers($product, $base, $line['added'] ?? [], $line['removed'] ?? []);

        return ['attributes' => [
            'product_id' => $product->id, 'menu_id' => $menu->id, 'product_name' => $product->translatedName($language), 'menu_name' => $menu->translatedName($language),
            'department_id' => $product->department_id, 'department_name' => $product->department?->name, 'requires_production' => (bool) $product->department?->production,
            'quantity' => $line['quantity'], 'base_price_cents' => $base, 'unit_price_cents' => $price, 'vat' => $product->vat, 'notes' => $line['notes'] ?? '',
        ], 'modifiers' => $modifiers];
    }

    public function append(OrderCourse $course, array $quote): OrderItem
    {
        $item = $course->items()->create($quote['attributes']);
        $item->modifiers()->createMany($quote['modifiers']);

        return $item;
    }

    private function check(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }
}
