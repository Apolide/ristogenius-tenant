<?php

namespace App\Livewire\Menu;

use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MenuIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $catalogSearch = '';

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

    public string $primaryColor = '#0f766e';

    public string $accentColor = '#d97706';

    public string $backgroundColor = '#fffaf0';

    public $menuImage;

    public ?string $existingMenuImagePath = null;

    public string $newCategoryIt = '';

    public string $newCategoryEn = '';

    public string $newCategoryDe = '';

    public $newCategoryImage;

    public bool $showCategoryForm = false;

    public ?string $editingCategoryId = null;

    public string $categoryNameIt = '';

    public string $categoryNameEn = '';

    public string $categoryNameDe = '';

    public string $categoryDescriptionIt = '';

    public string $categoryDescriptionEn = '';

    public string $categoryDescriptionDe = '';

    public bool $categoryIsEnabled = true;

    public $categoryImage;

    public ?string $existingCategoryImagePath = null;

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
        $this->primaryColor = data_get($menu->theme, 'primary', '#0f766e');
        $this->accentColor = data_get($menu->theme, 'accent', '#d97706');
        $this->backgroundColor = data_get($menu->theme, 'background', '#fffaf0');
        $this->existingMenuImagePath = $menu->image_path;
        $this->showMenuForm = true;
    }

    public function saveMenu(): void
    {
        $this->validate(['nameIt' => ['required', 'string', 'max:180'], 'enabledLanguages' => ['required', 'array', 'min:1'], 'enabledLanguages.*' => ['in:it,en,de'], 'serviceCharge' => ['numeric', 'min:0'], 'primaryColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], 'accentColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], 'backgroundColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], 'menuImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192']]);
        $imagePath = $this->existingMenuImagePath;
        if ($this->menuImage) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->menuImage->store('digital-menu/menus', 'public');
        }
        $menu = DigitalMenu::updateOrCreate(['id' => $this->menuId], ['slug' => Str::slug($this->nameIt).($this->menuId ? '' : '-'.Str::lower(Str::random(5))), 'name' => array_filter(['it' => $this->nameIt, 'en' => $this->nameEn, 'de' => $this->nameDe]), 'description' => array_filter(['it' => $this->descriptionIt, 'en' => $this->descriptionEn, 'de' => $this->descriptionDe]), 'enabled_languages' => $this->enabledLanguages, 'theme' => ['primary' => $this->primaryColor, 'accent' => $this->accentColor, 'background' => $this->backgroundColor, 'style' => 'editorial'], 'image_path' => $imagePath, 'service_charge' => $this->serviceCharge, 'is_published' => $this->isPublished, 'is_visible' => $this->isVisible]);
        $this->showMenuForm = false;
        $this->openEditor($menu->id);
        session()->flash('success', 'Menu salvato.');
    }

    public function openEditor(string $id): void
    {
        $this->menuId = $id;
        $this->mode = 'editor';
        $this->selectedCategoryId = DigitalMenu::findOrFail($id)->categories()->value('id');
        $this->resetPage('catalogPage');
    }

    public function updatingCatalogSearch(): void
    {
        $this->resetPage('catalogPage');
    }

    public function back(): void
    {
        $this->mode = 'list';
        $this->menuId = null;
        $this->selectedCategoryId = null;
    }

    public function createCategory(): void
    {
        $this->validate(['newCategoryIt' => ['required', 'string', 'max:150'], 'newCategoryImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        $category = DigitalMenuCategory::create(['digital_menu_id' => $this->menuId, 'name' => array_filter(['it' => $this->newCategoryIt, 'en' => $this->newCategoryEn, 'de' => $this->newCategoryDe]), 'image_path' => $this->newCategoryImage?->store('digital-menu/categories', 'public'), 'position' => DigitalMenuCategory::where('digital_menu_id', $this->menuId)->max('position') + 1]);
        $this->selectedCategoryId = $category->id;
        $this->reset(['newCategoryIt', 'newCategoryEn', 'newCategoryDe', 'newCategoryImage']);
    }

    public function editCategory(string $categoryId): void
    {
        $this->resetValidation();
        $category = DigitalMenuCategory::where('digital_menu_id', $this->menuId)->findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        foreach (['it' => 'It', 'en' => 'En', 'de' => 'De'] as $language => $suffix) {
            $this->{'categoryName'.$suffix} = $category->name[$language] ?? '';
            $this->{'categoryDescription'.$suffix} = $category->description[$language] ?? '';
        }
        $this->categoryIsEnabled = $category->is_enabled;
        $this->existingCategoryImagePath = $category->image_path;
        $this->reset('categoryImage');
        $this->showCategoryForm = true;
    }

    public function saveCategory(): void
    {
        $this->validate([
            'categoryNameIt' => ['required', 'string', 'max:150'],
            'categoryNameEn' => ['nullable', 'string', 'max:150'],
            'categoryNameDe' => ['nullable', 'string', 'max:150'],
            'categoryDescriptionIt' => ['nullable', 'string', 'max:1000'],
            'categoryDescriptionEn' => ['nullable', 'string', 'max:1000'],
            'categoryDescriptionDe' => ['nullable', 'string', 'max:1000'],
            'categoryImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $category = DigitalMenuCategory::where('digital_menu_id', $this->menuId)->findOrFail($this->editingCategoryId);
        $imagePath = $this->existingCategoryImagePath;
        if ($this->categoryImage) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->categoryImage->store('digital-menu/categories', 'public');
        }

        $category->update([
            'name' => array_filter(['it' => $this->categoryNameIt, 'en' => $this->categoryNameEn, 'de' => $this->categoryNameDe]),
            'description' => array_filter(['it' => $this->categoryDescriptionIt, 'en' => $this->categoryDescriptionEn, 'de' => $this->categoryDescriptionDe]),
            'image_path' => $imagePath,
            'is_enabled' => $this->categoryIsEnabled,
        ]);

        $this->showCategoryForm = false;
        session()->flash('success', 'Categoria aggiornata.');
    }

    public function removeCategoryImage(): void
    {
        if ($this->existingCategoryImagePath) {
            Storage::disk('public')->delete($this->existingCategoryImagePath);
            DigitalMenuCategory::where('digital_menu_id', $this->menuId)->whereKey($this->editingCategoryId)->update(['image_path' => null]);
        }

        $this->reset(['categoryImage', 'existingCategoryImagePath']);
    }

    public function deleteCategory(): void
    {
        $category = DigitalMenuCategory::where('digital_menu_id', $this->menuId)->findOrFail($this->editingCategoryId);
        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }
        $category->delete();

        $remainingIds = DigitalMenuCategory::where('digital_menu_id', $this->menuId)->orderBy('position')->orderBy('id')->pluck('id')->all();
        $this->persistCategoryOrder($remainingIds);
        $this->selectedCategoryId = $remainingIds[0] ?? null;
        $this->showCategoryForm = false;
        session()->flash('success', 'Categoria eliminata.');
    }

    public function moveCategory(string $categoryId, string $direction): void
    {
        abort_unless(in_array($direction, ['up', 'down'], true), 422);

        $categories = DigitalMenuCategory::query()
            ->where('digital_menu_id', $this->menuId)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
        $currentIndex = $categories->search(fn (DigitalMenuCategory $category) => $category->id === $categoryId);
        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if ($currentIndex === false || ! isset($categories[$targetIndex])) {
            return;
        }

        $categories->splice($targetIndex, 0, [$categories->splice($currentIndex, 1)->first()]);
        $this->persistCategoryOrder($categories->pluck('id')->all());
    }

    public function setCategoryPosition(string $categoryId, int $position): void
    {
        $categories = DigitalMenuCategory::query()
            ->where('digital_menu_id', $this->menuId)
            ->orderBy('position')
            ->orderBy('id')
            ->get();
        $currentIndex = $categories->search(fn (DigitalMenuCategory $category) => $category->id === $categoryId);

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = max(0, min(((int) $position) - 1, $categories->count() - 1));
        $categories->splice($targetIndex, 0, [$categories->splice($currentIndex, 1)->first()]);
        $this->persistCategoryOrder($categories->pluck('id')->all());
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
        $menu = DigitalMenu::with('categories')->findOrFail($id);
        Storage::disk('public')->delete(array_filter([$menu->image_path, ...$menu->categories->pluck('image_path')->all()]));
        $menu->delete();
        session()->flash('success', 'Menu eliminato.');
    }

    private function resetMenuForm(): void
    {
        $this->reset(['menuId', 'nameIt', 'nameEn', 'nameDe', 'descriptionIt', 'descriptionEn', 'descriptionDe', 'serviceCharge', 'isPublished', 'menuImage', 'existingMenuImagePath']);
        $this->enabledLanguages = ['it'];
        $this->isVisible = true;
        $this->primaryColor = '#0f766e';
        $this->accentColor = '#d97706';
        $this->backgroundColor = '#fffaf0';
    }

    public function removeMenuImage(): void
    {
        if ($this->existingMenuImagePath) {
            Storage::disk('public')->delete($this->existingMenuImagePath);
            DigitalMenu::whereKey($this->menuId)->update(['image_path' => null]);
        }

        $this->reset(['menuImage', 'existingMenuImagePath']);
    }

    /** @param array<int, string> $categoryIds */
    private function persistCategoryOrder(array $categoryIds): void
    {
        DB::transaction(function () use ($categoryIds): void {
            foreach ($categoryIds as $position => $categoryId) {
                DigitalMenuCategory::query()
                    ->where('digital_menu_id', $this->menuId)
                    ->whereKey($categoryId)
                    ->update(['position' => $position]);
            }
        });
    }

    public function render()
    {
        $menu = $this->menuId ? DigitalMenu::with(['categories.products.recommendations.recommendedProduct'])->find($this->menuId) : null;

        return view('livewire.menu.menu-index', [
            'menus' => DigitalMenu::withCount(['categories'])->when($this->search, fn ($q) => $q->whereTranslatedNameContains($this->search))->orderBy('position')->get(),
            'menu' => $menu,
            'products' => DigitalMenuProduct::query()
                ->where('is_active', true)
                ->when($this->catalogSearch, fn ($query) => $query->whereTranslatedNameContains($this->catalogSearch))
                ->orderBy('name')
                ->paginate(20, pageName: 'catalogPage'),
        ])->title('Gestione menu');
    }
}
