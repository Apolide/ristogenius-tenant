<?php

namespace App\Livewire\Menu;

use App\Models\Department;
use App\Models\DigitalMenuIngredient;
use App\Models\DigitalMenuProduct;
use App\Models\DigitalMenuProductRecommendation;
use App\Services\CustomerLanguageService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $departmentFilter = '';

    public bool $showForm = false;

    public ?string $editingId = null;

    public array $name = [];

    public array $description = [];

    public float $price = 0;

    public int $vat = 10;

    public ?string $departmentId = null;

    public array $allergens = [];

    public array $ingredientQuantities = [];

    public array $upsellIds = [];

    public array $crossSellIds = [];

    public bool $isActive = true;

    public bool $disallowTakeaway = false;

    public ?int $dailyPieces = null;

    public $image;

    public ?string $existingImagePath = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function quickIngredient(string $name): void
    {
        $name = trim($name);
        if ($name === '') {
            return;
        }
        $language = app(CustomerLanguageService::class)->default();
        $ingredient = DigitalMenuIngredient::firstOrCreate(['name->'.$language => $name], ['name' => [$language => $name], 'unit' => 'kg']);
        $this->ingredientQuantities[$ingredient->id] = 1;
    }

    public function edit(string $id): void
    {
        $product = DigitalMenuProduct::with(['ingredients', 'recommendations'])->findOrFail($id);
        $this->editingId = $product->id;
        $this->name = $product->name ?? [];
        $this->description = $product->description ?? [];
        $this->price = (float) $product->price;
        $this->vat = $product->vat;
        $this->departmentId = $product->department_id;
        $this->allergens = array_map(DigitalMenuProduct::canonicalAllergen(...), $product->allergens ?? []);
        $this->isActive = $product->is_active;
        $this->disallowTakeaway = $product->disallow_takeaway;
        $this->dailyPieces = $product->daily_pieces;
        $this->existingImagePath = $product->image_path;
        $this->ingredientQuantities = $product->ingredients->mapWithKeys(fn ($i) => [$i->id => (float) $i->pivot->quantity])->all();
        $this->upsellIds = $product->recommendations->where('type', DigitalMenuProductRecommendation::UPSELL)->pluck('recommended_product_id')->all();
        $this->crossSellIds = $product->recommendations->where('type', DigitalMenuProductRecommendation::CROSS_SELL)->pluck('recommended_product_id')->all();
        $this->showForm = true;
    }

    public function save(): void
    {
        $defaultLanguage = app(CustomerLanguageService::class)->default();
        $data = $this->validate(['name' => ['array:'.implode(',', array_keys(config('locales')))], 'name.*' => ['nullable', 'string', 'max:180'],
            'name.'.$defaultLanguage => ['required', 'string', 'max:180'],
            'description' => ['array:'.implode(',', array_keys(config('locales')))], 'description.*' => ['nullable', 'string', 'max:10000'], 'price' => ['required', 'numeric', 'min:0'], 'vat' => ['required', 'integer', 'between:0,100'], 'departmentId' => ['nullable', 'exists:departments,id'], 'dailyPieces' => ['nullable', 'integer', 'min:0'], 'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        $imagePath = $this->existingImagePath;
        if ($this->image) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->image->store('digital-menu/products', 'public');
        }
        $product = DigitalMenuProduct::updateOrCreate(['id' => $this->editingId], ['name' => array_filter($this->name, fn ($value) => $value !== null && $value !== ''), 'description' => array_filter($this->description, fn ($value) => $value !== null && $value !== ''), 'price' => $data['price'], 'vat' => $data['vat'], 'department_id' => $data['departmentId'], 'image_path' => $imagePath, 'allergens' => $this->allergens, 'is_active' => $this->isActive, 'disallow_takeaway' => $this->disallowTakeaway, 'daily_pieces' => $data['dailyPieces']]);
        $product->ingredients()->sync(collect($this->ingredientQuantities)->filter(fn ($qty) => is_numeric($qty) && $qty > 0)->map(fn ($qty) => ['quantity' => $qty])->all());
        $product->recommendations()->delete();
        foreach ([DigitalMenuProductRecommendation::UPSELL => $this->upsellIds, DigitalMenuProductRecommendation::CROSS_SELL => $this->crossSellIds] as $type => $ids) {
            foreach (array_unique($ids) as $position => $id) {
                if ($id !== $product->id) {
                    $product->recommendations()->create(['recommended_product_id' => $id, 'type' => $type, 'position' => $position]);
                }
            }
        }
        $this->showForm = false;
        session()->flash('success', __('menu.products.product_saved'));
    }

    public function delete(string $id): void
    {
        $product = DigitalMenuProduct::findOrFail($id);
        Storage::disk('public')->delete($product->image_path);
        $product->delete();
        session()->flash('success', __('menu.products.product_deleted'));
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'description', 'price', 'departmentId', 'allergens', 'ingredientQuantities', 'upsellIds', 'crossSellIds', 'dailyPieces', 'disallowTakeaway', 'image', 'existingImagePath']);
        $this->vat = 10;
        $this->isActive = true;
    }

    public function removeImage(): void
    {
        if ($this->existingImagePath) {
            Storage::disk('public')->delete($this->existingImagePath);
            DigitalMenuProduct::whereKey($this->editingId)->update(['image_path' => null]);
        }

        $this->reset(['image', 'existingImagePath']);
    }

    public function render(CustomerLanguageService $languages)
    {
        $query = DigitalMenuProduct::with(['department', 'recommendations'])->withCount('ingredients')->when($this->search, fn ($q) => $q->whereTranslatedNameContains($this->search))->when($this->departmentFilter, fn ($q) => $q->where('department_id', $this->departmentFilter));

        return view('livewire.menu.product-index', ['languageMeta' => $languages->enabled(), 'defaultLanguage' => $languages->default(), 'products' => $query->latest()->paginate(15), 'departments' => Department::orderBy('name')->get(), 'ingredients' => DigitalMenuIngredient::orderBy('name')->get(), 'recommendableProducts' => DigitalMenuProduct::when($this->editingId, fn ($q) => $q->whereKeyNot($this->editingId))->orderBy('name')->get()])->title(__('menu.products.products'));
    }
}
