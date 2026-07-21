<?php

namespace App\Livewire\Menu;

use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuProduct;
use Illuminate\Support\Str;
use Livewire\Component;

class MenuIndex extends Component
{
    public string $search = '';

    public string $mode = 'list';

    public ?string $menuId = null;

    public ?string $selectedCategoryId = null;

    public bool $showMenuForm = false;

    public string $nameIt = '';

    public string $nameEn = '';

    public string $nameDe = '';

    public string $descriptionIt = '';

    public string $descriptionEn = '';

    public string $descriptionDe = '';

    public array $enabledLanguages = ['it'];

    public bool $isPublished = false;

    public bool $isVisible = true;

    public float $serviceCharge = 0;

    public string $newCategoryIt = '';

    public string $newCategoryEn = '';

    public string $newCategoryDe = '';

    public function create(): void
    {
        $this->resetMenuForm();
        $this->showMenuForm = true;
    }

    public function editMenu(string $id): void
    {
        $menu = DigitalMenu::findOrFail($id);
        $this->menuId = $menu->id;
        foreach (['it' => 'It', 'en' => 'En', 'de' => 'De'] as $lang => $suffix) {
            $this->{'name'.$suffix} = $menu->name[$lang] ?? '';
            $this->{'description'.$suffix} = $menu->description[$lang] ?? '';
        }
        $this->enabledLanguages = $menu->enabled_languages;
        $this->isPublished = $menu->is_published;
        $this->isVisible = $menu->is_visible;
        $this->serviceCharge = (float) $menu->service_charge;
        $this->showMenuForm = true;
    }

    public function saveMenu(): void
    {
        $this->validate(['nameIt' => ['required', 'string', 'max:180'], 'enabledLanguages' => ['required', 'array', 'min:1'], 'enabledLanguages.*' => ['in:it,en,de'], 'serviceCharge' => ['numeric', 'min:0']]);
        $menu = DigitalMenu::updateOrCreate(['id' => $this->menuId], ['slug' => Str::slug($this->nameIt).($this->menuId ? '' : '-'.Str::lower(Str::random(5))), 'name' => array_filter(['it' => $this->nameIt, 'en' => $this->nameEn, 'de' => $this->nameDe]), 'description' => array_filter(['it' => $this->descriptionIt, 'en' => $this->descriptionEn, 'de' => $this->descriptionDe]), 'enabled_languages' => $this->enabledLanguages, 'theme' => ['primary' => '#0f766e', 'style' => 'editorial'], 'service_charge' => $this->serviceCharge, 'is_published' => $this->isPublished, 'is_visible' => $this->isVisible]);
        $this->showMenuForm = false;
        $this->openEditor($menu->id);
        session()->flash('success', 'Menu salvato.');
    }

    public function openEditor(string $id): void
    {
        $this->menuId = $id;
        $this->mode = 'editor';
        $this->selectedCategoryId = DigitalMenu::findOrFail($id)->categories()->value('id');
    }

    public function back(): void
    {
        $this->mode = 'list';
        $this->menuId = null;
        $this->selectedCategoryId = null;
    }

    public function createCategory(): void
    {
        $this->validate(['newCategoryIt' => ['required', 'string', 'max:150']]);
        $category = DigitalMenuCategory::create(['digital_menu_id' => $this->menuId, 'name' => array_filter(['it' => $this->newCategoryIt, 'en' => $this->newCategoryEn, 'de' => $this->newCategoryDe]), 'position' => DigitalMenuCategory::where('digital_menu_id', $this->menuId)->max('position') + 1]);
        $this->selectedCategoryId = $category->id;
        $this->reset(['newCategoryIt', 'newCategoryEn', 'newCategoryDe']);
    }

    public function addProduct(string $productId): void
    {
        $category = DigitalMenuCategory::where('digital_menu_id', $this->menuId)->findOrFail($this->selectedCategoryId);
        if (! $category->products()->whereKey($productId)->exists()) {
            $category->products()->attach($productId, ['position' => $category->products()->count()]);
        }
    }

    public function removeProduct(string $productId): void
    {
        DigitalMenuCategory::where('digital_menu_id', $this->menuId)->findOrFail($this->selectedCategoryId)->products()->detach($productId);
    }

    public function moveProduct(string $productId, string $direction): void
    {
        $category = DigitalMenuCategory::findOrFail($this->selectedCategoryId);
        $items = $category->products()->get();
        $index = $items->search(fn ($p) => $p->id === $productId);
        $swap = $direction === 'up' ? $index - 1 : $index + 1;
        if ($index === false || ! isset($items[$swap])) {
            return;
        }
        $category->products()->updateExistingPivot($items[$index]->id, ['position' => $swap]);
        $category->products()->updateExistingPivot($items[$swap]->id, ['position' => $index]);
    }

    public function publish(): void
    {
        DigitalMenu::findOrFail($this->menuId)->update(['is_published' => true]);
        session()->flash('success', 'Menu pubblicato.');
    }

    public function delete(string $id): void
    {
        DigitalMenu::findOrFail($id)->delete();
        session()->flash('success', 'Menu eliminato.');
    }

    private function resetMenuForm(): void
    {
        $this->reset(['menuId', 'nameIt', 'nameEn', 'nameDe', 'descriptionIt', 'descriptionEn', 'descriptionDe', 'serviceCharge', 'isPublished']);
        $this->enabledLanguages = ['it'];
        $this->isVisible = true;
    }

    public function render()
    {
        $menu = $this->menuId ? DigitalMenu::with(['categories.products.recommendations.recommendedProduct'])->find($this->menuId) : null;

        return view('livewire.menu.menu-index', ['menus' => DigitalMenu::withCount(['categories'])->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))->orderBy('position')->get(), 'menu' => $menu, 'products' => DigitalMenuProduct::where('is_active', true)->orderBy('name')->get()])->title('Gestione menu');
    }
}
