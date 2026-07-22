<?php

namespace Tests\Feature\DigitalMenu;

use App\Livewire\Menu\IngredientIndex;
use App\Livewire\Menu\MenuIndex;
use App\Livewire\Menu\ProductIndex;
use App\Models\Department;
use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalMenuLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_ingredient_can_be_created_edited_searched_and_deleted(): void
    {
        Livewire::test(IngredientIndex::class)
            ->call('create')
            ->set('nameIt', 'Burrata pugliese')
            ->set('nameEn', 'Apulian burrata')
            ->set('unit', 'pz')
            ->set('stockQuantity', 12)
            ->set('minimumQuantity', 3)
            ->set('addPrice', 3)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $ingredient = DigitalMenuIngredient::firstOrFail();
        $this->assertSame('Burrata pugliese', $ingredient->name['it']);

        Livewire::test(IngredientIndex::class)
            ->call('edit', $ingredient->id)
            ->set('stockQuantity', 20)
            ->set('removePrice', -1)
            ->call('save')
            ->assertHasNoErrors()
            ->set('search', 'Burrata')
            ->assertSee('Burrata pugliese')
            ->call('delete', $ingredient->id);

        $this->assertDatabaseMissing('digital_menu_ingredients', ['id' => $ingredient->id]);
    }

    public function test_product_can_be_saved_with_recipe_allergens_and_recommendations(): void
    {
        $department = Department::create(['name' => 'Cucina', 'production' => true]);
        $ingredient = DigitalMenuIngredient::create(['name' => ['it' => 'Polpo'], 'unit' => 'kg']);
        $upsell = $this->product('Polpo XL', 28);
        $crossSell = $this->product('Verdeca', 7);

        Livewire::test(ProductIndex::class)
            ->call('create')
            ->set('nameIt', 'Polpo alla brace')
            ->set('nameEn', 'Grilled octopus')
            ->set('descriptionIt', 'Polpo locale e crema di patate')
            ->set('price', 20)
            ->set('vat', 10)
            ->set('departmentId', $department->id)
            ->set('allergens', ['molluschi'])
            ->set('ingredientQuantities', [$ingredient->id => 0.250])
            ->set('upsellIds', [$upsell->id])
            ->set('crossSellIds', [$crossSell->id])
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $product = DigitalMenuProduct::where('name->it', 'Polpo alla brace')->firstOrFail();
        $this->assertSame($ingredient->id, $product->ingredients->first()->id);
        $this->assertSame('0.250', $product->ingredients->first()->pivot->quantity);
        $this->assertEqualsCanonicalizing(['upsell', 'cross_sell'], $product->recommendations->pluck('type')->all());
        $this->assertSame(['molluschi'], $product->allergens);
    }

    public function test_product_validation_requires_an_italian_name_and_valid_price(): void
    {
        Livewire::test(ProductIndex::class)
            ->call('create')
            ->set('nameIt', '')
            ->set('price', -1)
            ->call('save')
            ->assertHasErrors(['nameIt' => 'required', 'price' => 'min']);
    }

    public function test_menu_can_be_created_and_products_managed_in_a_category(): void
    {
        $productA = $this->product('Orecchiette', 14);
        $productB = $this->product('Linguine alle vongole', 18);

        $component = Livewire::test(MenuIndex::class)
            ->call('create')
            ->set('nameIt', 'Menu Ristorante')
            ->set('nameEn', 'Restaurant menu')
            ->set('enabledLanguages', ['it', 'en'])
            ->set('serviceCharge', 2.50)
            ->call('saveMenu')
            ->assertHasNoErrors()
            ->assertSet('mode', 'editor')
            ->set('newCategoryIt', 'Primi')
            ->set('newCategoryEn', 'First courses')
            ->call('createCategory');

        $menu = DigitalMenu::where('name->it', 'Menu Ristorante')->firstOrFail();
        $category = $menu->categories()->firstOrFail();

        $component
            ->assertSet('selectedCategoryId', $category->id)
            ->call('addProduct', $productA->id)
            ->call('addProduct', $productB->id)
            ->call('moveProduct', $productB->id, 'up');

        $this->assertSame([$productB->id, $productA->id], $category->products()->pluck('digital_menu_products.id')->all());

        $component->call('removeProduct', $productA->id)->call('publish');
        $this->assertTrue($menu->fresh()->is_published);
        $this->assertDatabaseMissing('digital_menu_category_product', ['menu_category_id' => $category->id, 'product_id' => $productA->id]);
    }

    public function test_categories_can_be_ordered_with_arrows_or_a_selected_position(): void
    {
        $menu = $this->menu();
        $starters = $menu->categories()->create(['name' => ['it' => 'Antipasti'], 'position' => 0]);
        $mains = $menu->categories()->create(['name' => ['it' => 'Primi'], 'position' => 1]);
        $desserts = $menu->categories()->create(['name' => ['it' => 'Dolci'], 'position' => 2]);

        $component = Livewire::test(MenuIndex::class)
            ->call('openEditor', $menu->id)
            ->call('moveCategory', $desserts->id, 'up');

        $this->assertSame(
            [$starters->id, $desserts->id, $mains->id],
            $menu->categories()->pluck('id')->all(),
        );

        $component
            ->call('setCategoryPosition', $starters->id, 3)
            ->call('editMenu', $menu->id)
            ->assertSee('Ordine categorie');

        $this->assertSame(
            [$desserts->id, $mains->id, $starters->id],
            $menu->categories()->pluck('id')->all(),
        );
        $this->assertSame([0, 1, 2], $menu->categories()->pluck('position')->all());
    }

    public function test_product_catalog_is_paginated_twenty_per_page_and_searchable(): void
    {
        foreach (range(1, 21) as $index) {
            $this->product('Prodotto '.str_pad((string) $index, 2, '0', STR_PAD_LEFT), $index);
        }
        $this->product('Prodotto nascosto', 99)->update(['is_active' => false]);

        Livewire::test(MenuIndex::class)
            ->assertViewHas('products', fn ($products) => $products->perPage() === 20 && $products->total() === 21 && $products->count() === 20)
            ->set('catalogSearch', 'Prodotto 21')
            ->assertViewHas('products', fn ($products) => $products->total() === 1 && $products->first()->translatedName('it') === 'Prodotto 21')
            ->assertSet('paginators.catalogPage', 1);
    }

    public function test_admin_routes_require_authentication_and_admin_role(): void
    {
        $this->get(route('menu.products'))->assertRedirect();
        $this->get(route('menu.ingredients'))->assertRedirect();
        $this->get(route('menu.menus'))->assertRedirect();

        $this->seed(RoleSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get(route('menu.products'))->assertOk();
        $this->actingAs($admin)->get(route('menu.ingredients'))->assertOk();
        $this->actingAs($admin)->get(route('menu.menus'))->assertOk();
    }

    public function test_public_menu_is_visible_only_when_published_visible_and_language_enabled(): void
    {
        $menu = $this->menu();
        $category = DigitalMenuCategory::create(['digital_menu_id' => $menu->id, 'name' => ['it' => 'Secondi', 'en' => 'Mains'], 'position' => 0]);
        $product = $this->product('Pescato del giorno', 24);
        $category->products()->attach($product->id, ['position' => 0, 'is_visible' => true]);

        $this->get(route('menu.public', ['language' => 'it', 'menu' => $menu]))
            ->assertOk()
            ->assertSee('Menu Mare')
            ->assertSee('Pescato del giorno')
            ->assertSee('id="menu-language"', false)
            ->assertSee(route('menu.public', ['language' => 'en', 'menu' => $menu]), false);

        $this->get(route('menu.public', ['language' => 'de', 'menu' => $menu]))->assertNotFound();
        $menu->update(['is_published' => false]);
        $this->get(route('menu.public', ['language' => 'it', 'menu' => $menu]))->assertNotFound();
    }

    private function product(string $name, float $price): DigitalMenuProduct
    {
        return DigitalMenuProduct::create(['name' => ['it' => $name, 'en' => $name], 'price' => $price, 'vat' => 10, 'is_active' => true]);
    }

    private function menu(): DigitalMenu
    {
        return DigitalMenu::create(['slug' => 'menu-mare', 'name' => ['it' => 'Menu Mare', 'en' => 'Sea menu'], 'enabled_languages' => ['it', 'en'], 'theme' => [], 'is_published' => true, 'is_visible' => true]);
    }
}
