<?php

namespace App\Livewire\Menu;

use App\Models\DigitalMenuIngredient;
use App\Services\CustomerLanguageService;
use Livewire\Component;
use Livewire\WithPagination;

class IngredientIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $editingId = null;

    public bool $showForm = false;

    public array $name = [];

    public string $unit = 'kg';

    public float $stockQuantity = 0;

    public float $minimumQuantity = 0;

    public float $addPrice = 0;

    public float $removePrice = 0;

    public bool $tracked = true;

    public bool $isFrozen = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(string $id): void
    {
        $ingredient = DigitalMenuIngredient::findOrFail($id);
        $this->editingId = $ingredient->id;
        $this->name = $ingredient->name ?? [];
        $this->unit = $ingredient->unit;
        $this->stockQuantity = (float) $ingredient->stock_quantity;
        $this->minimumQuantity = (float) $ingredient->minimum_quantity;
        $this->addPrice = (float) $ingredient->add_price;
        $this->removePrice = (float) $ingredient->remove_price;
        $this->tracked = $ingredient->tracked;
        $this->isFrozen = $ingredient->is_frozen;
        $this->showForm = true;
    }

    public function save(): void
    {
        $defaultLanguage = app(CustomerLanguageService::class)->default();
        $data = $this->validate([
            'name' => ['array:'.implode(',', array_keys(config('locales')))], 'name.*' => ['nullable', 'string', 'max:150'],
            'name.'.$defaultLanguage => ['required', 'string', 'max:150'],
            'unit' => ['required', 'in:kg,g,l,ml,pz'], 'stockQuantity' => ['required', 'numeric', 'min:0'], 'minimumQuantity' => ['required', 'numeric', 'min:0'],
            'addPrice' => ['required', 'numeric'], 'removePrice' => ['required', 'numeric'], 'tracked' => ['boolean'], 'isFrozen' => ['boolean'],
        ]);
        DigitalMenuIngredient::updateOrCreate(['id' => $this->editingId], [
            'name' => array_filter($data['name'], fn ($value) => $value !== null && $value !== ''), 'unit' => $data['unit'],
            'stock_quantity' => $data['stockQuantity'], 'minimum_quantity' => $data['minimumQuantity'], 'add_price' => $data['addPrice'],
            'remove_price' => $data['removePrice'], 'tracked' => $data['tracked'], 'is_frozen' => $data['isFrozen'],
        ]);
        $this->showForm = false;
        session()->flash('success', __('menu.ingredients.ingredient_saved'));
    }

    public function delete(string $id): void
    {
        DigitalMenuIngredient::findOrFail($id)->delete();
        session()->flash('success', __('menu.ingredients.ingredient_deleted'));
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'stockQuantity', 'minimumQuantity', 'addPrice', 'removePrice', 'isFrozen']);
        $this->unit = 'kg';
        $this->tracked = true;
    }

    public function render(CustomerLanguageService $languages)
    {
        return view('livewire.menu.ingredient-index', ['languageMeta' => $languages->enabled(), 'defaultLanguage' => $languages->default(), 'ingredients' => DigitalMenuIngredient::query()->withCount('products')->when($this->search, fn ($q) => $q->whereTranslatedNameContains($this->search))->latest()->paginate(15)])->title(__('menu.common.ingredients'));
    }
}
