<?php

namespace App\Livewire\Orders;

use App\Models\DigitalMenu;
use App\Models\DigitalMenuCategory;
use App\Models\DigitalMenuIngredient;
use App\Models\Order;
use App\Services\Orders\OrderService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Editor extends Component
{
    #[Locked]
    public string $orderId;

    public string $menuId = '';

    public string $categoryId = '';

    public string $search = '';

    public string $courseId = '';

    public int $covers = 1;

    public bool $productModal = false;

    #[Locked]
    public string $productId = '';

    #[Locked]
    public string $productCategoryId = '';

    #[Locked]
    public ?string $editingItemId = null;

    public int $quantity = 1;

    public string $notes = '';

    public array $added = [];

    public array $removed = [];

    public function boot(): void
    {
        app(OrderService::class)->authorize();
    }

    public function mount(Order $order): void
    {
        abort_unless($order->fulfillment === 'dine_in', 404);
        $this->orderId = $order->id;
        $this->covers = $order->covers;
        $this->menuId = (string) DigitalMenu::where('is_visible', true)->orderBy('position')->value('id');
        $this->selectFirstCategory();
        $this->courseId = (string) $order->courses()->whereNull('sent_at')->whereNull('submission_key')->orderByDesc('number')->value('id');
    }

    private function selectFirstCategory(): void
    {
        $this->categoryId = (string) DigitalMenuCategory::where('digital_menu_id', $this->menuId)->where('is_enabled', true)->orderBy('position')->value('id');
    }

    public function updatedMenuId(): void
    {
        $this->selectFirstCategory();
        $this->search = '';
    }

    public function addCourse(): void
    {
        app(OrderService::class)->addCourse($this->orderId);
        $this->courseId = (string) Order::findOrFail($this->orderId)->courses()->orderByDesc('number')->value('id');
    }

    public function updateCovers(): void
    {
        app(OrderService::class)->covers($this->orderId, $this->covers);
    }

    public function chooseProduct(string $productId): void
    {
        $category = DigitalMenuCategory::where('digital_menu_id', $this->menuId)->findOrFail($this->categoryId);
        $category->products()->findOrFail($productId);
        $this->reset(['quantity', 'notes', 'added', 'removed', 'editingItemId']);
        $this->resetValidation();
        $this->productId = $productId;
        $this->productCategoryId = $category->id;
        $this->productModal = true;
    }

    public function editItem(string $id): void
    {
        $item = Order::findOrFail($this->orderId)->items()->findOrFail($id);
        $this->resetValidation();
        $this->editingItemId = $item->id;
        $this->productId = $item->product_id ?? '';
        $this->added = $item->modifiers()->where('action', 'add')->whereNotNull('ingredient_id')->pluck('ingredient_id')->all();
        $this->removed = $item->modifiers()->where('action', 'remove')->whereNotNull('ingredient_id')->pluck('ingredient_id')->all();
        $this->productCategoryId = '';
        $this->quantity = $item->quantity;
        $this->notes = $item->notes ?? '';
        $this->productModal = true;
    }

    public function saveItem(): void
    {
        $courseId = $this->editingItemId ? Order::findOrFail($this->orderId)->items()->findOrFail($this->editingItemId)->order_course_id : $this->courseId;
        app(OrderService::class)->saveItem($this->orderId, $courseId, $this->productCategoryId, $this->productId, $this->quantity, $this->notes, $this->added, $this->removed, $this->editingItemId);
        $this->productModal = false;
    }

    public function removeItem(string $id): void
    {
        app(OrderService::class)->removeItem($this->orderId, $id);
    }

    public function sendCourse(string $id): void
    {
        app(OrderService::class)->send($this->orderId, $id);
    }

    public function rejectCourse(string $id): void
    {
        app(OrderService::class)->rejectCourse($this->orderId, $id);
    }

    public function render()
    {
        $order = Order::with(['courses.items.modifiers', 'courses.items.ticket', 'courses.tickets'])->findOrFail($this->orderId);
        $menu = DigitalMenu::where('is_visible', true)->find($this->menuId);
        $categories = $menu ? $menu->categories()->where('is_enabled', true)->get() : collect();
        $category = $categories->firstWhere('id', $this->categoryId);
        $products = $category ? $category->products()->where('is_active', true)->wherePivot('is_visible', true)->get()->filter(fn ($p) => $this->search === '' || mb_stripos($p->translatedName(), $this->search) !== false) : collect();
        $product = $this->productModal && $this->productId !== '' ? \App\Models\DigitalMenuProduct::with('ingredients')->find($this->productId) : null;

        return view('livewire.orders.editor', compact('order', 'categories', 'products', 'product') + [
            'menus' => DigitalMenu::where('is_visible', true)->orderBy('position')->get(),
            'ingredients' => $product ? DigitalMenuIngredient::all()->sortBy(fn ($i) => $i->translatedName()) : collect(),
        ])->title(__('Comanda · ').$order->table_name);
    }
}
