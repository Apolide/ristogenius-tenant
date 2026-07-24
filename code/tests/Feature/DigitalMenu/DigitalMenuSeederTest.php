<?php

namespace Tests\Feature\DigitalMenu;

use App\Models\DigitalMenu;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\DigitalMenuProductRecommendation;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\DigitalMenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalMenuSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_three_complete_multilingual_menus(): void
    {
        $this->seed(DepartmentSeeder::class);
        $this->seed(DigitalMenuSeeder::class);

        $this->assertSame(3, DigitalMenu::count());
        $this->assertSame(23, DigitalMenuProduct::count());
        $this->assertSame(26, DigitalMenuIngredient::count());
        $this->assertSame(10, DigitalMenuProductRecommendation::count());
        $this->assertDatabaseHas('digital_menu_menus', ['slug' => 'menu-bar-spiaggia', 'is_published' => true]);
        $this->assertDatabaseHas('digital_menu_menus', ['slug' => 'menu-ristorante-pizzeria', 'is_published' => true]);
        $this->assertDatabaseHas('digital_menu_menus', ['slug' => 'menu-piatti-del-giorno', 'is_published' => true]);

        foreach (DigitalMenu::with('categories.products')->get() as $menu) {
            $this->assertSame(['it', 'en', 'de'], $menu->enabled_languages);
            $this->assertNotEmpty($menu->name['it']);
            $this->assertNotEmpty($menu->name['en']);
            $this->assertNotEmpty($menu->name['de']);
            $this->assertTrue($menu->is_published);
            $this->assertTrue($menu->is_visible);
            $this->assertNotEmpty($menu->categories);
            $this->assertTrue($menu->categories->every(fn ($category) => $category->products->isNotEmpty()));
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(DepartmentSeeder::class);
        $this->seed(DigitalMenuSeeder::class);
        $counts = $this->counts();

        $this->seed(DigitalMenuSeeder::class);

        $this->assertSame($counts, $this->counts());
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        return [
            'menus' => DigitalMenu::count(),
            'products' => DigitalMenuProduct::count(),
            'ingredients' => DigitalMenuIngredient::count(),
            'recommendations' => DigitalMenuProductRecommendation::count(),
        ];
    }
}
