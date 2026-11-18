<?php

namespace App\Livewire\Menu;

use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuProduct;
use App\Services\CustomerLanguageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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

    public array $name = [];

    public array $description = [];

    public array $enabledLanguages = ['it'];

    public bool $isPublished = false;

    public bool $isVisible = true;

    public float $serviceCharge = 0;

    public string $primaryColor = '#0f766e';

    public string $accentColor = '#d97706';

    public string $backgroundColor = '#fffaf0';

    public $menuImage;

    public ?string $existingMenuImagePath = null;

    public array $newCategory = [];

    public $newCategoryImage;

    public bool $showCategoryForm = false;

    public ?string $editingCategoryId = null;

    public array $categoryName = [];

    public array $categoryDescription = [];

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
        $this->name = $menu->name ?? [];
        $this->description = $menu->description ?? [];
        $this->enabledLanguages = array_values(array_intersect($menu->enabled_languages, array_keys(app(CustomerLanguageService::class)->enabled())));
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
        $defaultLanguage = app(CustomerLanguageService::class)->default();
        $this->validate(['name' => ['array:'.implode(',', array_keys(config('locales')))], 'name.*' => ['nullable', 'string', 'max:180'],
            'name.'.$defaultLanguage => ['required', 'string', 'max:180'],
            'description' => ['array:'.implode(',', array_keys(config('locales')))], 'description.*' => ['nullable', 'string', 'max:10000'], 'enabledLanguages' => ['required', 'array', 'min:1'], 'enabledLanguages.*' => [Rule::in(array_keys(app(CustomerLanguageService::class)->enabled()))], 'serviceCharge' => ['numeric', 'min:0'], 'primaryColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], 'accentColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], 'backgroundColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'], 'menuImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192']]);
        $imagePath = $this->existingMenuImagePath;
        if ($this->menuImage) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->menuImage->store('digital-menu/menus', 'public');
        }
        $menu = DigitalMenu::updateOrCreate(['id' => $this->menuId], ['slug' => Str::slug($this->name[$defaultLanguage]).($this->menuId ? '' : '-'.Str::lower(Str::random(5))), 'name' => array_filter($this->name, fn ($value) => $value !== null && $value !== ''), 'description' => array_filter($this->description, fn ($value) => $value !== null && $value !== ''), 'enabled_languages' => $this->enabledLanguages, 'theme' => ['primary' => $this->primaryColor, 'accent' => $this->accentColor, 'background' => $this->backgroundColor, 'style' => 'editorial'], 'image_path' => $imagePath, 'service_charge' => $this->serviceCharge, 'is_published' => $this->isPublished, 'is_visible' => $this->isVisible]);
        $this->showMenuForm = false;
        $this->openEditor($menu->id);
        session()->flash('success', __('menu.management.menu_saved'));
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
        $defaultLanguage = app(CustomerLanguageService::class)->default();
        $this->validate(['newCategory' => ['array:'.implode(',', array_keys(config('locales')))], 'newCategory.*' => ['nullable', 'string', 'max:150'],
            'newCategory.'.$defaultLanguage => ['required', 'string', 'max:150'], 'newCategoryImage' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        $category = DigitalMenuCategory::create(['digital_menu_id' => $this->menuId, 'name' => array_filter($this->newCategory, fn ($value) => $value !== null && $value !== ''), 'image_path' => $this->newCategoryImage?->store('digital-menu/categories', 'public'), 'position' => DigitalMenuCategory::where('digital_menu_id', $this->menuId)->max('position') + 1]);
        $this->selectedCategoryId = $category->id;
        $this->reset(['newCategory', 'newCategoryImage']);
    }

    public function editCategory(string $categoryId): void
    {
        $this->resetValidation();
        $category = DigitalMenuCategory::where('digital_menu_id', $this->menuId)->findOrFail($categoryId);
        $this->editingCategoryId = $category->id;
        $this->categoryName = $category->name ?? [];
        $this->categoryDescription = $category->description ?? [];
        $this->categoryIsEnabled = $category->is_enabled;
        $this->existingCategoryImagePath = $category->image_path;
        $this->reset('categoryImage');
        $this->showCategoryForm = true;
    }

    public function saveCategory(): void
    {
        $defaultLanguage = app(CustomerLanguageService::class)->default();
        $this->validate([
            'categoryName' => ['array:'.implode(',', array_keys(config('locales')))], 'categoryName.*' => ['nullable', 'string', 'max:150'],
            'categoryName.'.$defaultLanguage => ['required', 'string', 'max:150'],
            'categoryDescription' => ['array:'.implode(',', array_keys(config('locales')))], 'categoryDescription.*' => ['nullable', 'string', 'max:1000'],
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
            'name' => array_filter($this->categoryName, fn ($value) => $value !== null && $value !== ''),
            'description' => array_filter($this->categoryDescription, fn ($value) => $value !== null && $value !== ''),
            'image_path' => $imagePath,
            'is_enabled' => $this->categoryIsEnabled,
        ]);

        $this->showCategoryForm = false;
        session()->flash('success', __('menu.management.category_updated'));
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
        session()->flash('success', __('menu.management.category_deleted'));
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
        session()->flash('success', __('menu.management.menu_published'));
    }

    public function delete(string $id): void
    {
        $menu = DigitalMenu::with('categories')->findOrFail($id);
        Storage::disk('public')->delete(array_filter([$menu->image_path, ...$menu->categories->pluck('image_path')->all()]));
        $menu->delete();
        session()->flash('success', __('menu.management.menu_deleted'));
    }

    private function resetMenuForm(): void
    {
        $this->reset(['menuId', 'name', 'description', 'serviceCharge', 'isPublished', 'menuImage', 'existingMenuImagePath']);
        $this->enabledLanguages = [app(CustomerLanguageService::class)->default()];
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

    public function render(CustomerLanguageService $languages)
    {
        $menu = $this->menuId ? DigitalMenu::with(['categories.products.recommendations.recommendedProduct'])->find($this->menuId) : null;

        return view('livewire.menu.menu-index', [
            'languageMeta' => $languages->enabled(),
            'defaultLanguage' => $languages->default(),
            'menus' => DigitalMenu::withCount(['categories'])->when($this->search, fn ($q) => $q->whereTranslatedNameContains($this->search))->orderBy('position')->get(),
            'menu' => $menu,
            'products' => DigitalMenuProduct::query()
                ->where('is_active', true)
                ->when($this->catalogSearch, fn ($query) => $query->whereTranslatedNameContains($this->catalogSearch))
                ->orderBy('name')
                ->paginate(20, pageName: 'catalogPage'),
        ])->title(__('menu.management.menu_management'));
    }
}
