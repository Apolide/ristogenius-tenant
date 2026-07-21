<?php

namespace Tests\Feature\DigitalMenu;

use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\DigitalMenuProductRecommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalMenuModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_use_prefixed_tables_and_resolve_translations(): void
    {
        $menu = $this->menu();
        $product = $this->product();
        $ingredient = $this->ingredient();

        $this->assertSame('digital_menu_menus', $menu->getTable());
        $this->assertSame('digital_menu_categories', (new DigitalMenuCategory)->getTable());
        $this->assertSame('digital_menu_products', $product->getTable());
        $this->assertSame('digital_menu_ingredients', $ingredient->getTable());
        $this->assertSame('digital_menu_product_recommendations', (new DigitalMenuProductRecommendation)->getTable());
        $this->assertSame('Dinner menu', $menu->translatedName('en'));
        $this->assertSame('Menu Cena', $menu->translatedName('de'));
        $this->assertSame('Grilled octopus', $product->translatedName('en'));
    }

    public function test_menu_category_product_and_ingredient_relations_are_persisted(): void
    {
        $menu = $this->menu();
        $category = $menu->categories()->create([
            'name' => ['it' => 'Secondi', 'en' => 'Mains'],
            'position' => 0,
            'is_enabled' => true,
        ]);
        $product = $this->product();
        $ingredient = $this->ingredient();

        $product->ingredients()->attach($ingredient->id, ['quantity' => 0.250]);
        $category->products()->attach($product->id, [
            'menu_price' => 22.50,
            'badge' => json_encode(['it' => 'Consigliato']),
            'position' => 0,
            'is_visible' => true,
        ]);

        $this->assertSame($category->id, $menu->fresh()->categories->first()->id);
        $this->assertSame($product->id, $category->fresh()->products->first()->id);
        $this->assertSame('22.50', $category->fresh()->products->first()->pivot->menu_price);
        $this->assertSame($ingredient->id, $product->fresh()->ingredients->first()->id);
        $this->assertSame('0.250', $product->fresh()->ingredients->first()->pivot->quantity);
        $this->assertDatabaseHas('digital_menu_category_product', ['menu_category_id' => $category->id, 'product_id' => $product->id]);
        $this->assertDatabaseHas('digital_menu_ingredient_product', ['ingredient_id' => $ingredient->id, 'product_id' => $product->id]);
    }

    public function test_product_can_have_upsell_and_cross_sell_recommendations(): void
    {
        $source = $this->product();
        $upsell = $this->product('Polpo XL', 28);
        $crossSell = $this->product('Calice di Verdeca', 7);

        $source->recommendations()->create([
            'recommended_product_id' => $upsell->id,
            'type' => DigitalMenuProductRecommendation::UPSELL,
            'placement' => 'product',
            'position' => 0,
        ]);
        $source->recommendations()->create([
            'recommended_product_id' => $crossSell->id,
            'type' => DigitalMenuProductRecommendation::CROSS_SELL,
            'placement' => 'cart',
            'position' => 1,
        ]);

        $recommendations = $source->fresh()->recommendations;
        $this->assertCount(2, $recommendations);
        $this->assertSame($upsell->id, $recommendations->firstWhere('type', 'upsell')->recommendedProduct->id);
        $this->assertSame($crossSell->id, $recommendations->firstWhere('type', 'cross_sell')->recommendedProduct->id);
    }

    public function test_deleting_a_menu_cascades_categories_and_pivots(): void
    {
        $menu = $this->menu();
        $category = $menu->categories()->create(['name' => ['it' => 'Primi'], 'position' => 0]);
        $product = $this->product();
        $category->products()->attach($product->id);

        $menu->delete();

        $this->assertDatabaseMissing('digital_menu_categories', ['id' => $category->id]);
        $this->assertDatabaseMissing('digital_menu_category_product', ['menu_category_id' => $category->id]);
        $this->assertDatabaseHas('digital_menu_products', ['id' => $product->id]);
    }

    private function menu(): DigitalMenu
    {
        return DigitalMenu::create([
            'slug' => 'menu-cena',
            'name' => ['it' => 'Menu Cena', 'en' => 'Dinner menu'],
            'description' => ['it' => 'Cucina mediterranea'],
            'enabled_languages' => ['it', 'en'],
            'theme' => ['primary' => '#0f766e'],
            'is_published' => true,
            'is_visible' => true,
        ]);
    }

    private function product(string $name = 'Polpo alla brace', float $price = 20): DigitalMenuProduct
    {
        return DigitalMenuProduct::create([
            'name' => ['it' => $name, 'en' => $name === 'Polpo alla brace' ? 'Grilled octopus' : $name],
            'description' => ['it' => 'Pescato locale'],
            'price' => $price,
            'vat' => 10,
            'allergens' => ['molluschi'],
            'is_active' => true,
        ]);
    }

    private function ingredient(): DigitalMenuIngredient
    {
        return DigitalMenuIngredient::create([
            'name' => ['it' => 'Polpo', 'en' => 'Octopus'],
            'unit' => 'kg',
            'stock_quantity' => 10,
            'minimum_quantity' => 2,
            'tracked' => true,
        ]);
    }
}
